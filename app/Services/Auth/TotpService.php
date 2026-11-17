<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TotpService
{
    private const BASE32_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * Genera un secreto Base32 de 160 bits compatible con RFC 6238 (Google Authenticator / Authy).
     */
    public function generateSecret(int $bytes = 20): string
    {
        $raw = random_bytes($bytes);
        $bits = '';
        for ($i = 0, $len = strlen($raw); $i < $len; $i++) {
            $bits .= str_pad(decbin(ord($raw[$i])), 8, '0', STR_PAD_LEFT);
        }

        $secret = '';
        foreach (str_split($bits, 5) as $chunk) {
            if (strlen($chunk) < 5) {
                $chunk = str_pad($chunk, 5, '0', STR_PAD_RIGHT);
            }
            $secret .= self::BASE32_ALPHABET[bindec($chunk)];
        }

        return $secret;
    }

    /**
     * Construye la URI otpauth:// estándar para escanear en apps autenticadoras.
     */
    public function getProvisioningUri(string $issuer, string $email, string $secret): string
    {
        $label = rawurlencode($issuer . ':' . $email);
        $params = http_build_query([
            'secret' => $secret,
            'issuer' => $issuer,
            'algorithm' => 'SHA1',
            'digits' => 6,
            'period' => 30,
        ]);

        return "otpauth://totp/{$label}?{$params}";
    }

    /**
     * Calcula el código TOTP de 6 dígitos para un instante dado (usado también en pruebas).
     */
    public function getCodeForTimestamp(string $secret, ?int $timestamp = null): string
    {
        $timeSlice = (int) floor(($timestamp ?? time()) / 30);
        $key = $this->base32Decode($secret);
        $timeBytes = pack('N*', 0) . pack('N*', $timeSlice);
        $hash = hash_hmac('sha1', $timeBytes, $key, true);
        $offset = ord($hash[strlen($hash) - 1]) & 0x0F;
        $value = (
            ((ord($hash[$offset]) & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) << 8) |
            (ord($hash[$offset + 3]) & 0xFF)
        ) % 1000000;

        return str_pad((string) $value, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Verifica un código TOTP de 6 dígitos con ventana de tolerancia (±30s).
     */
    public function verifyCode(string $secret, string $code, int $window = 1): bool
    {
        $normalized = preg_replace('/\s+/', '', trim($code));
        if (!preg_match('/^\d{6}$/', $normalized)) {
            return false;
        }

        $now = time();
        for ($i = -$window; $i <= $window; $i++) {
            $expected = $this->getCodeForTimestamp($secret, $now + ($i * 30));
            if (hash_equals($expected, $normalized)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Genera 8 códigos de respaldo de un solo uso y sus hashes bcrypt.
     *
     * @return array{plain: array<int, string>, hashed: array<int, string>}
     */
    public function generateRecoveryCodes(int $count = 8): array
    {
        $plain = [];
        $hashed = [];

        for ($i = 0; $i < $count; $i++) {
            $code = strtoupper(Str::random(5) . '-' . Str::random(5));
            $plain[] = $code;
            $hashed[] = Hash::make($code);
        }

        return ['plain' => $plain, 'hashed' => $hashed];
    }

    /**
     * Valida y consume un código de respaldo (un solo uso) de forma atómica.
     */
    public function consumeRecoveryCode(User $user, string $code): bool
    {
        $normalized = strtoupper(trim($code));
        if ($normalized === '') {
            return false;
        }

        return DB::transaction(function () use ($user, $normalized): bool {
            $fresh = User::query()->lockForUpdate()->find($user->id);
            if (!$fresh) {
                return false;
            }

            $codes = is_array($fresh->two_factor_recovery_codes) ? $fresh->two_factor_recovery_codes : [];
            foreach ($codes as $idx => $hashedCode) {
                if (is_string($hashedCode) && Hash::check($normalized, $hashedCode)) {
                    unset($codes[$idx]);
                    $fresh->two_factor_recovery_codes = array_values($codes);
                    $fresh->save();
                    return true;
                }
            }

            return false;
        });
    }

    private function base32Decode(string $secret): string
    {
        $clean = strtoupper(preg_replace('/[^A-Z2-7]/', '', $secret));
        $bits = '';
        for ($i = 0, $len = strlen($clean); $i < $len; $i++) {
            $pos = strpos(self::BASE32_ALPHABET, $clean[$i]);
            if ($pos === false) {
                continue;
            }
            $bits .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
        }

        $binary = '';
        foreach (str_split($bits, 8) as $byte) {
            if (strlen($byte) === 8) {
                $binary .= chr(bindec($byte));
            }
        }

        return $binary;
    }
}
