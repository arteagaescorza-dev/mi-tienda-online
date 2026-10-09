<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Requests\Storefront\AuthModalGoogleLoginRequest;
use App\Http\Requests\Storefront\AuthModalGoogleRegisterRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuthModalController extends Controller
{
    public function login(Request $request): JsonResponse|RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($credentials)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Credenciales invalidas. Verifica tu correo y contrasena.',
                ], 422);
            }

            return back()
                ->withErrors(['email' => 'Credenciales invalidas. Verifica tu correo y contrasena.'])
                ->withInput($request->only('email'));
        }

        $request->session()->regenerate();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Sesion iniciada correctamente.',
                'user' => $this->userPayload($request->user()),
            ]);
        }

        $user = $request->user();
        if ($user && in_array($user->role, ['portal', 'admin'], true)) {
            return redirect()->intended(route('portal.dashboard'));
        }

        return redirect()->intended('/');
    }

    public function logout(Request $request): JsonResponse|RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Sesion cerrada correctamente.',
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
            'message' => 'Sesion iniciada con Google.',
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
            'message' => 'Cuenta creada con Google e inicio de sesion completado.',
            'user' => $this->userPayload($user),
        ]);
    }

    private function userPayload(User $user): array
    {
        $type = $this->resolveUserType($user);

        return [
            'name' => $user->name,
            'email' => $user->email,
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
