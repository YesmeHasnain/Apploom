<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;

class TokenVault
{
    public function encrypt(?string $plain): ?string
    {
        return $plain === null ? null : Crypt::encryptString($plain);
    }

    public function decrypt(?string $enc): ?string
    {
        if ($enc === null) return null;
        try { return Crypt::decryptString($enc); }
        catch (\Throwable) { return null; }
    }
}
