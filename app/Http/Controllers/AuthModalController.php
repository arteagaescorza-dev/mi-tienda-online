<?php

namespace App\Http\Controllers;

use App\Http\Requests\Storefront\AuthModalGoogleLoginRequest;
use App\Http\Requests\Storefront\AuthModalGoogleRegisterRequest;
use App\Models\User;
use App\Notifications\Auth\ResetBuyerPasswordNotification;
use App\Notifications\Auth\VerifyBuyerEmailNotification;
use App\Services\Auth\CustomerOrderLinkingService;
use App\Services\Auth\TotpService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthModalController extends Controller
{
    private const TRUSTED_DEVICE_COOKIE = 'mto_trusted_device';
    private const TRUSTED_DEVICE_DAYS = 30;

    public function login(Request $request): JsonResponse|RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $email = strtolower(trim($credentials['email']));
        $rateKey = 'buyer-login:' . $email . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($rateKey, 5)) {
            $seconds = RateLimiter::availableIn($rateKey);
            $msg = "Demasiados intentos fallidos. Intenta nuevamente en {$seconds} segundos.";

            if ($request->expectsJson()) {
                return response()->json(['message' => $msg], 429);
            }

            return back()->withErrors(['email' => $msg])->withInput($request->only('email'));
        }

        $user = User::query()->where('email', $email)->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            RateLimiter::hit($rateKey, 60);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Credenciales inválidas. Verifica tu correo y contraseña.',
                ], 422);
            }

            return back()
                ->withErrors(['email' => 'Credenciales inválidas. Verifica tu correo y contraseña.'])
                ->withInput($request->only('email'));
        }

        RateLimiter::clear($rateKey);

        if ($user->hasTwoFactorEnabled() && !$this->hasValidTrustedDeviceCookie($request, $user)) {
            $request->session()->put('auth.two_factor_pending', [
                'user_id' => $user->id,
                'expires_at' => now()->addMinutes(10)->timestamp,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'requires_two_factor' => true,
                    'message' => 'Ingresa el código de 6 dígitos de tu app autenticadora o un código de respaldo.',
                ]);
            }

            return redirect()->route('login')->with('two_factor_required', true);
        }

        Auth::login($user);
        $request->session()->regenerate();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Sesión iniciada correctamente.',
                'user' => $this->userPayload($user),
            ]);
        }

        if (in_array($user->role, ['portal', 'admin', 'superadmin'], true)) {
            return redirect()->intended(route('portal.dashboard'));
        }

        return redirect()->intended('/');
    }

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:190', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'phone' => $validated['phone'] ?? null,
            'password' => $validated['password'],
            'role' => 'customer',
        ]);

        $mailSent = true;
        $mailError = null;

        try {
            $user->notify(new VerifyBuyerEmailNotification());
        } catch (\Throwable $e) {
            $mailSent = false;
            $mailError = $e->getMessage();
            report($e);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'message' => $mailSent
                ? 'Cuenta creada correctamente. Te enviamos un correo para confirmar tu cuenta.'
                : 'Cuenta creada, pero no se pudo enviar el correo de confirmación (' . $mailError . ').',
            'email_sent' => $mailSent,
            'user' => $this->userPayload($user),
        ], 201);
    }

    public function verifyEmail(
        Request $request,
        int $id,
        string $hash,
        CustomerOrderLinkingService $orderLinker
    ): JsonResponse|RedirectResponse {
        $user = User::query()->findOrFail($id);

        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            abort(403, 'Enlace de verificación inválido.');
        }

        $linkedCount = DB::transaction(function () use ($user, $orderLinker): int {
            if (!$user->hasVerifiedEmail()) {
                $user->markEmailAsVerified();
            }

            return $orderLinker->linkGuestOrdersForVerifiedUser($user);
        });

        if (!Auth::check()) {
            Auth::login($user);
            $request->session()->regenerate();
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Correo confirmado correctamente.',
                'linked_orders' => $linkedCount,
                'user' => $this->userPayload($user->fresh()),
            ]);
        }

        return redirect('/?tab=account&verified=1');
    }

    public function resendVerification(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Tu correo electrónico ya está verificado.',
                'user' => $this->userPayload($user),
            ]);
        }

        $rateKey = 'buyer-verify-resend:' . $user->id;
        if (RateLimiter::tooManyAttempts($rateKey, 3)) {
            $seconds = RateLimiter::availableIn($rateKey);
            return response()->json([
                'message' => "Espera {$seconds} segundos antes de reenviar el correo.",
            ], 429);
        }

        RateLimiter::hit($rateKey, 60);

        try {
            $user->notify(new VerifyBuyerEmailNotification());
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'message' => 'No se pudo enviar el correo de verificación: ' . $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => 'Correo de verificación reenviado. Revisa tu bandeja de entrada.',
        ]);
    }

    public function twoFactorChallenge(Request $request, TotpService $totp): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string'],
            'trust_device' => ['nullable', 'boolean'],
        ]);

        $pending = $request->session()->get('auth.two_factor_pending');
        if (!is_array($pending) || ($pending['expires_at'] ?? 0) < now()->timestamp) {
            $request->session()->forget('auth.two_factor_pending');
            return response()->json([
                'message' => 'Tu sesión de verificación expiró. Inicia sesión nuevamente.',
            ], 422);
        }

        $user = User::query()->find($pending['user_id'] ?? null);
        if (!$user || !$user->hasTwoFactorEnabled()) {
            $request->session()->forget('auth.two_factor_pending');
            return response()->json([
                'message' => 'No hay una verificación 2FA pendiente válida.',
            ], 422);
        }

        $rateKey = 'buyer-2fa:' . $user->id . '|' . $request->ip();
        if (RateLimiter::tooManyAttempts($rateKey, 5)) {
            $seconds = RateLimiter::availableIn($rateKey);
            return response()->json([
                'message' => "Demasiados intentos 2FA. Intenta en {$seconds} segundos.",
            ], 429);
        }

        $code = trim((string) $validated['code']);
        if (!$this->verifyTwoFactorOrRecovery($user, $code, $totp)) {
            RateLimiter::hit($rateKey, 60);
            return response()->json([
                'message' => 'Código de verificación o respaldo inválido.',
            ], 422);
        }

        RateLimiter::clear($rateKey);
        $request->session()->forget('auth.two_factor_pending');

        Auth::login($user);
        $request->session()->regenerate();

        $response = response()->json([
            'message' => 'Verificación en dos pasos completada.',
            'user' => $this->userPayload($user->fresh()),
        ]);

        if ($request->boolean('trust_device')) {
            $response->withCookie($this->makeTrustedDeviceCookie($user));
        }

        return $response;
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = strtolower(trim($validated['email']));
        $rateKey = 'buyer-forgot:' . $email . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($rateKey, 5)) {
            $seconds = RateLimiter::availableIn($rateKey);
            return response()->json([
                'message' => "Demasiadas solicitudes. Intenta nuevamente en {$seconds} segundos.",
            ], 429);
        }

        RateLimiter::hit($rateKey, 60);

        $user = User::query()->where('email', $email)->first();
        if ($user) {
            $token = Str::random(64);
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $email],
                ['token' => Hash::make($token), 'created_at' => now()]
            );

            try {
                $user->notify(new ResetBuyerPasswordNotification($token));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return response()->json([
            'message' => 'Si el correo existe en nuestro sistema, recibirás un enlace para restablecer tu contraseña.',
        ]);
    }

    public function resetPassword(Request $request, TotpService $totp): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'token' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'code' => ['nullable', 'string'],
        ]);

        $email = strtolower(trim($validated['email']));
        $record = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (
            !$record
            || Carbon::parse($record->created_at)->addMinutes(60)->isPast()
            || !Hash::check($validated['token'], $record->token)
        ) {
            return response()->json([
                'message' => 'El enlace de recuperación es inválido o ha expirado.',
            ], 422);
        }

        $user = User::query()->where('email', $email)->first();
        if (!$user) {
            return response()->json([
                'message' => 'El enlace de recuperación es inválido o ha expirado.',
            ], 422);
        }

        if ($user->hasTwoFactorEnabled()) {
            $code = trim((string) ($validated['code'] ?? ''));
            if ($code === '') {
                return response()->json([
                    'requires_two_factor' => true,
                    'message' => 'Tu cuenta tiene 2FA activo. Ingresa tu código de 6 dígitos o un código de respaldo.',
                ], 422);
            }

            if (!$this->verifyTwoFactorOrRecovery($user, $code, $totp)) {
                return response()->json([
                    'requires_two_factor' => true,
                    'message' => 'Código 2FA o de respaldo inválido.',
                ], 422);
            }
        }

        DB::transaction(function () use ($user, $validated, $email): void {
            $user->password = $validated['password'];
            $user->two_factor_trusted_version = ((int) ($user->two_factor_trusted_version ?? 1)) + 1;
            $user->save();

            DB::table('password_reset_tokens')->where('email', $email)->delete();
            DB::table('sessions')->where('user_id', $user->id)->delete();
        });

        return response()
            ->json(['message' => 'Contraseña restablecida correctamente. Ya puedes iniciar sesión.'])
            ->withCookie(Cookie::forget(self::TRUSTED_DEVICE_COOKIE));
    }

    public function enableTwoFactor(Request $request, TotpService $totp): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (!$user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Debes confirmar tu correo electrónico antes de activar 2FA.',
            ], 403);
        }

        if ($user->hasTwoFactorEnabled()) {
            return response()->json([
                'message' => 'La verificación en dos pasos ya está activa.',
            ], 422);
        }

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'message' => 'La contraseña actual es incorrecta.',
            ], 422);
        }

        $secret = $totp->generateSecret();
        $request->session()->put('auth.two_factor_setup_secret', $secret);

        return response()->json([
            'secret' => $secret,
            'otpauth_uri' => $totp->getProvisioningUri((string) config('app.name', 'Mi Tienda Online'), $user->email, $secret),
            'message' => 'Escanea el código QR o ingresa la clave en tu app autenticadora y confirma con un código de 6 dígitos.',
        ]);
    }

    public function confirmTwoFactor(Request $request, TotpService $totp): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string'],
        ]);

        /** @var User $user */
        $user = $request->user();
        $secret = $request->session()->get('auth.two_factor_setup_secret');

        if (!is_string($secret) || $secret === '') {
            return response()->json([
                'message' => 'Primero inicia la configuración de 2FA.',
            ], 422);
        }

        if (!$totp->verifyCode($secret, trim($validated['code']))) {
            return response()->json([
                'message' => 'El código de 6 dígitos es incorrecto. Intenta nuevamente.',
            ], 422);
        }

        $codes = $totp->generateRecoveryCodes(8);

        $user->two_factor_secret = $secret;
        $user->two_factor_recovery_codes = $codes['hashed'];
        $user->two_factor_confirmed = true;
        $user->two_factor_trusted_version = ((int) ($user->two_factor_trusted_version ?? 1)) + 1;
        $user->save();

        $request->session()->forget('auth.two_factor_setup_secret');

        return response()->json([
            'message' => 'Verificación en dos pasos activada. Guarda tus 8 códigos de respaldo en un lugar seguro.',
            'recovery_codes' => $codes['plain'],
            'user' => $this->userPayload($user->fresh()),
        ]);
    }

    public function disableTwoFactor(Request $request, TotpService $totp): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (!$user->hasTwoFactorEnabled()) {
            return response()->json([
                'message' => 'La verificación en dos pasos no está activa.',
            ], 422);
        }

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'code' => ['required', 'string'],
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'message' => 'La contraseña actual es incorrecta.',
            ], 422);
        }

        if (!$this->verifyTwoFactorOrRecovery($user, trim($validated['code']), $totp)) {
            return response()->json([
                'message' => 'El código de verificación o respaldo es inválido.',
            ], 422);
        }

        $user->two_factor_secret = null;
        $user->two_factor_recovery_codes = null;
        $user->two_factor_confirmed = false;
        $user->two_factor_trusted_version = ((int) ($user->two_factor_trusted_version ?? 1)) + 1;
        $user->save();

        return response()
            ->json([
                'message' => 'Verificación en dos pasos desactivada.',
                'user' => $this->userPayload($user->fresh()),
            ])
            ->withCookie(Cookie::forget(self::TRUSTED_DEVICE_COOKIE));
    }

    public function regenerateRecoveryCodes(Request $request, TotpService $totp): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (!$user->hasTwoFactorEnabled()) {
            return response()->json([
                'message' => 'Debes tener 2FA activo para regenerar códigos de respaldo.',
            ], 422);
        }

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'code' => ['required', 'string'],
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'message' => 'La contraseña actual es incorrecta.',
            ], 422);
        }

        if (!$this->verifyTwoFactorOrRecovery($user, trim($validated['code']), $totp)) {
            return response()->json([
                'message' => 'El código de verificación o respaldo es inválido.',
            ], 422);
        }

        $codes = $totp->generateRecoveryCodes(8);
        $user->two_factor_recovery_codes = $codes['hashed'];
        $user->save();

        return response()->json([
            'message' => 'Se generaron 8 nuevos códigos de respaldo.',
            'recovery_codes' => $codes['plain'],
        ]);
    }

    public function changePassword(Request $request, TotpService $totp): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'code' => ['nullable', 'string'],
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'message' => 'La contraseña actual es incorrecta.',
            ], 422);
        }

        if ($user->hasTwoFactorEnabled()) {
            $code = trim((string) ($validated['code'] ?? ''));
            if ($code === '' || !$this->verifyTwoFactorOrRecovery($user, $code, $totp)) {
                return response()->json([
                    'message' => 'Ingresa un código 2FA o de respaldo válido para cambiar tu contraseña.',
                ], 422);
            }
        }

        $currentSessionId = $request->session()->getId();

        DB::transaction(function () use ($user, $validated, $currentSessionId): void {
            $user->password = $validated['password'];
            $user->two_factor_trusted_version = ((int) ($user->two_factor_trusted_version ?? 1)) + 1;
            $user->save();

            DB::table('sessions')
                ->where('user_id', $user->id)
                ->where('id', '!=', $currentSessionId)
                ->delete();
        });

        return response()
            ->json([
                'message' => 'Contraseña actualizada correctamente.',
                'user' => $this->userPayload($user->fresh()),
            ])
            ->withCookie(Cookie::forget(self::TRUSTED_DEVICE_COOKIE));
    }

    public function logout(Request $request): JsonResponse|RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Sesión cerrada correctamente.',
            ]);
        }

        return redirect()->route('login');
    }

    public function googleLogin(AuthModalGoogleLoginRequest $request): JsonResponse
    {
        $user = User::query()->firstOrCreate(
            ['email' => 'google.user@mitienda.com'],
            [
                'name' => 'Google User',
                'password' => Str::password(24),
                'role' => 'customer',
            ]
        );

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'message' => 'Sesión iniciada con Google.',
            'user' => $this->userPayload($user),
        ]);
    }

    public function googleRegister(AuthModalGoogleRegisterRequest $request): JsonResponse
    {
        $suffix = random_int(1000, 9999);

        $user = User::query()->create([
            'name' => 'Google Nuevo',
            'email' => "nuevo.google.{$suffix}@mitienda.com",
            'password' => Str::password(24),
            'role' => 'customer',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'message' => 'Cuenta creada con Google e inicio de sesión completado.',
            'user' => $this->userPayload($user),
        ]);
    }

    private function verifyTwoFactorOrRecovery(User $user, string $code, TotpService $totp): bool
    {
        $secret = (string) ($user->two_factor_secret ?? '');
        if ($secret !== '' && $totp->verifyCode($secret, $code)) {
            return true;
        }

        return $totp->consumeRecoveryCode($user, $code);
    }

    private function hasValidTrustedDeviceCookie(Request $request, User $user): bool
    {
        $raw = $request->cookie(self::TRUSTED_DEVICE_COOKIE);
        if (!is_string($raw) || $raw === '') {
            return false;
        }

        try {
            $payload = json_decode(Crypt::decryptString($raw), true);
        } catch (\Throwable) {
            return false;
        }

        if (!is_array($payload)) {
            return false;
        }

        return (int) ($payload['user_id'] ?? 0) === (int) $user->id
            && (int) ($payload['version'] ?? -1) === (int) ($user->two_factor_trusted_version ?? 1)
            && (int) ($payload['expires_at'] ?? 0) > now()->timestamp;
    }

    private function makeTrustedDeviceCookie(User $user)
    {
        $payload = Crypt::encryptString((string) json_encode([
            'user_id' => $user->id,
            'version' => (int) ($user->two_factor_trusted_version ?? 1),
            'expires_at' => now()->addDays(self::TRUSTED_DEVICE_DAYS)->timestamp,
        ]));

        return Cookie::make(
            self::TRUSTED_DEVICE_COOKIE,
            $payload,
            self::TRUSTED_DEVICE_DAYS * 24 * 60,
            '/',
            null,
            config('session.secure', false),
            true,
            false,
            'lax'
        );
    }

    private function userPayload(User $user): array
    {
        $type = $this->resolveUserType($user);

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role,
            'email_verified' => $user->hasVerifiedEmail(),
            'two_factor_enabled' => $user->hasTwoFactorEnabled(),
            'avatar' => 'https://i.pravatar.cc/80?u=' . urlencode($user->email),
            'roleLabel' => $type === 'portal' ? 'Usuario portal' : 'Cliente de la tienda',
            'type' => $type,
        ];
    }

    private function resolveUserType(User $user): string
    {
        return $user->role === 'portal' ? 'portal' : 'customer';
    }
}
