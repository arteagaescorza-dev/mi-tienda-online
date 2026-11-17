<?php

namespace App\Http\Controllers;

use App\Models\CustomerAddress;
use App\Models\User;
use App\Services\Auth\TotpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class BuyerAccountController extends Controller
{
    public function updatePersonalInfo(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30'],
            'discrete_packaging' => ['nullable', 'boolean'],
        ]);

        $user->name = trim($validated['name']);
        $user->phone = isset($validated['phone']) && trim($validated['phone']) !== ''
            ? trim($validated['phone'])
            : null;

        if (
            array_key_exists('discrete_packaging', $validated)
            && Schema::hasColumn('users', 'discrete_packaging')
        ) {
            $user->discrete_packaging = (bool) $validated['discrete_packaging'];
        }

        $user->save();

        return response()->json([
            'message' => 'Información personal guardada en tu cuenta.',
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'discrete_packaging' => (bool) ($user->discrete_packaging ?? true),
            ],
        ]);
    }

    public function addresses(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'addresses' => $this->formatAddresses($user),
        ]);
    }

    public function storeAddress(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $validated = $this->validateAddress($request);

        $address = DB::transaction(function () use ($user, $validated): CustomerAddress {
            $hasAny = $user->addresses()->exists();
            $makeDefault = !$hasAny || !empty($validated['is_default']);

            if ($makeDefault) {
                $user->addresses()->update(['is_default' => false]);
            }

            return $user->addresses()->create([
                'label_type' => $validated['label_type'],
                'street' => trim($validated['street']),
                'postal_code' => trim($validated['postal_code']),
                'colony' => trim($validated['colony']),
                'city' => trim($validated['city']),
                'state' => trim($validated['state']),
                'references' => isset($validated['references']) ? trim((string) $validated['references']) : null,
                'is_default' => $makeDefault,
            ]);
        });

        return response()->json([
            'message' => 'Dirección guardada en tu libreta.',
            'address' => $this->serializeAddress($address),
            'addresses' => $this->formatAddresses($user),
        ], 201);
    }

    public function updateAddress(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $address = CustomerAddress::query()
            ->where('user_id', $user->id)
            ->findOrFail($id);

        $validated = $this->validateAddress($request);

        DB::transaction(function () use ($user, $address, $validated): void {
            if (!empty($validated['is_default'])) {
                $user->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
            }

            $address->update([
                'label_type' => $validated['label_type'],
                'street' => trim($validated['street']),
                'postal_code' => trim($validated['postal_code']),
                'colony' => trim($validated['colony']),
                'city' => trim($validated['city']),
                'state' => trim($validated['state']),
                'references' => isset($validated['references']) ? trim((string) $validated['references']) : null,
                'is_default' => !empty($validated['is_default']) ? true : $address->is_default,
            ]);
        });

        return response()->json([
            'message' => 'Dirección actualizada.',
            'addresses' => $this->formatAddresses($user),
        ]);
    }

    public function setDefaultAddress(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $address = CustomerAddress::query()
            ->where('user_id', $user->id)
            ->findOrFail($id);

        DB::transaction(function () use ($user, $address): void {
            $user->addresses()->update(['is_default' => false]);
            $address->update(['is_default' => true]);
        });

        return response()->json([
            'message' => 'Dirección predeterminada actualizada.',
            'addresses' => $this->formatAddresses($user),
        ]);
    }

    public function deleteAddress(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $address = CustomerAddress::query()
            ->where('user_id', $user->id)
            ->findOrFail($id);

        DB::transaction(function () use ($user, $address): void {
            $wasDefault = $address->is_default;
            $address->delete();

            if ($wasDefault) {
                $next = $user->addresses()->orderByDesc('id')->first();
                if ($next) {
                    $next->update(['is_default' => true]);
                }
            }
        });

        return response()->json([
            'message' => 'Dirección eliminada.',
            'addresses' => $this->formatAddresses($user),
        ]);
    }

    public function deleteAccount(Request $request, TotpService $totp): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'code' => ['nullable', 'string'],
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'message' => 'La contraseña actual es incorrecta.',
            ], 422);
        }

        if ($user->hasTwoFactorEnabled()) {
            $code = trim((string) ($validated['code'] ?? ''));
            $secret = (string) ($user->two_factor_secret ?? '');
            $validTotp = $secret !== '' && $totp->verifyCode($secret, $code);
            if ($code === '' || (!$validTotp && !$totp->consumeRecoveryCode($user, $code))) {
                return response()->json([
                    'message' => 'Ingresa un código 2FA o de respaldo válido para eliminar tu cuenta.',
                    'requires_two_factor' => true,
                ], 422);
            }
        }

        DB::transaction(function () use ($user): void {
            if (Schema::hasTable('customer_addresses')) {
                $user->addresses()->delete();
            }

            $user->name = 'Cuenta Eliminada';
            $user->email = 'deleted_' . $user->id . '_' . Str::random(8) . '@anonymized.local';
            $user->phone = null;
            $user->password = Hash::make(Str::random(32));
            $user->two_factor_secret = null;
            $user->two_factor_recovery_codes = null;
            $user->two_factor_confirmed = false;
            if (Schema::hasColumn('users', 'two_factor_trusted_version')) {
                $user->two_factor_trusted_version = ((int) ($user->two_factor_trusted_version ?? 1)) + 1;
            }
            if (Schema::hasColumn('users', 'anonymized_at')) {
                $user->anonymized_at = now();
            }
            $user->save();

            if (Schema::hasTable('sessions')) {
                DB::table('sessions')->where('user_id', $user->id)->delete();
            }
        });

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()
            ->json(['message' => 'Tu cuenta y datos personales fueron eliminados conforme a tus derechos ARCO.'])
            ->withCookie(Cookie::forget('mto_trusted_device'));
    }

    private function validateAddress(Request $request): array
    {
        return $request->validate([
            'label_type' => ['required', 'string', 'in:casa,trabajo'],
            'street' => ['required', 'string', 'max:190'],
            'postal_code' => ['required', 'string', 'max:10'],
            'colony' => ['required', 'string', 'max:120'],
            'city' => ['required', 'string', 'max:120'],
            'state' => ['required', 'string', 'max:120'],
            'references' => ['nullable', 'string', 'max:255'],
            'is_default' => ['nullable', 'boolean'],
        ]);
    }

    private function formatAddresses(User $user): array
    {
        if (!Schema::hasTable('customer_addresses')) {
            return [];
        }

        return $user->addresses()
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->get()
            ->map(fn (CustomerAddress $a) => $this->serializeAddress($a))
            ->values()
            ->all();
    }

    private function serializeAddress(CustomerAddress $address): array
    {
        return [
            'id' => $address->id,
            'type' => $address->label_type,
            'street' => $address->street,
            'postalCode' => $address->postal_code,
            'colony' => $address->colony,
            'city' => $address->city,
            'state' => $address->state,
            'references' => $address->references ?? '',
            'isDefault' => (bool) $address->is_default,
        ];
    }
}
