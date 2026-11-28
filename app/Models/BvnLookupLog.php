<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class BvnLookupLog extends Model
{
    protected $guarded = [];
    
    protected function bvn(): Attribute
    {
        return Attribute::make(
            set: fn(string $value) => Crypt::encryptString($value),
        );
    }
    protected function bvnHash(): Attribute
    {
        return Attribute::make(
            set: fn(string $value) => hash_hmac(
                "sha256",
                $value,
                config("app.key"),
            ),
        );
    }
    protected function rawResponse(): Attribute
    {
        return Attribute::make(
            set: fn(string $value) => Crypt::encryptString($value),
        );
    }
    public static function getBvnRecord(string $bvn): string | null
    {
        $record = Cache::get(self::getCacheKey($bvn));
        if($record) return $record;
        $record = self::where("bvn_hash", hash_hmac("sha256", $bvn,  config("app.key")))->first();
        if (!$record) {
            return null;
        }
        return Crypt::encryptString(json_encode($record));
    }
    public static function getCacheKey(string $bvn): string
    {
        return "bvn:lookup:" . $bvn;
    }
}
