<?php

-----------------------------------------
ACCESS CONFIG MIGRATION COLUMNS
-----------------------------------------

Schema::create('access_configs', function (Blueprint $table) {
    $table->id();
    $table->string("access_code")->unique();
    $table->boolean("is_active")->default(0);
    $table->timestamps();
});

-----------------------------------
ACCESS CHECK MIDDLEWARE
-----------------------------------

$accessCode = $request->header("AccessCode");
if(!$accessCode){
    return response()->json([
        "status" => false,
        "message" => "Unauthorise"
    ], 401);
}
$accessConfig = AccessConfig::where("access_code", $accessCode)->where("is_active", 1)->first();
if(!$accessConfig){
    return response()->json(["status" => false,"message" => "Unauthorise"], 401);
}
return $next($request);

------------------------------------------------
BVN LOOKUP LOG MIGRATION COLUMNS
-------------------------------------------------

Schema::create('bvn_lookup_logs', function (Blueprint $table) {
    $table->id();
    $table->string('first_name');
    $table->text('last_name');
    $table->text('bvn');
    $table->string('bvn_hash');
    $table->text('raw_response');
    $table->text('raw_request');
     $table->string('provider');
    $table->timestamps();
});



----------------------------------------
BVN LOOKUP LOG MODEL IMPLEMENTATION
-----------------------------------------

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
    //$record = Cache::get(self::getCacheKey($bvn));
    ///if($record) return $record;
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

-----------------------------------------------------
PROVIDERS MIGRATION COLUMNS
------------------------------------------------------

Schema::create('providers', function (Blueprint $table) {
    $table->id();
    $table->string('name')->unique();
    $table->string('class_path');
    $table->boolean('is_active')->default(0);
    $table->bigInteger('ttl_success')->default(0);
    $table->bigInteger('ttl_failed')->default(0);
    $table->timestamps();
});


---------------------------------
IDENTITY SERVICE
---------------------------------

<?php

namespace App\Services;

use App\Exceptions\CustomException;
use App\Models\BvnLookupLog;
use App\ThirdParty\IdentityProviders\Contract\ProviderContract;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Cache;

class IdentityService
{
    public function __construct(private ProviderContract $identityProvider) {}
    public function getBvnDetails(array $request): string
    {
        $dbRecord = BvnLookupLog::getBvnRecord($request["bvn"]);
        if ( $dbRecord ) return $dbRecord;

        $providerRecord = $this->identityProvider->getBvnDetails(...$request);
        
        if (  !$providerRecord || strtolower($providerRecord->first_name) != strtolower($request["first_name"]) &&
                strtolower($providerRecord->last_name) != strtolower(request["last_name"])
        ) {
            throw new CustomException("Identity mismatch", 400);
        }
        
        $dbRecord = BvnLookupLog::create([
            "first_name" => $providerRecord->first_name,
            "last_name" => $providerRecord->last_name,
            "bvn" => $providerRecord->bvn,
            "bvn_hash" => $providerRecord->bvn,
            "raw_response" => $providerRecord->raw_response,
            "raw_request" => $providerRecord->raw_request,
            "provider" => $this->identityProvider->getProviderName()
        ]);
        $encryptedResponse = Crypt::encryptString(json_encode($dbRecord));
        //Cache::put(BvnLookupLog::getCacheKey($request["bvn"]), $encryptedResponse );
        return $encryptedResponse;
    }
    
}



-----------------------------------
PROVIDER CONTRACT
------------------------------------


<?php
namespace App\ThirdParty\IdentityProviders\Contract;

use App\ThirdParty\IdentityProviders\Dto\GetBvnDetailsDto;


interface ProviderContract {
    public function getBvnDetails( string ...$param): GetBvnDetailsDto;
    public function getProviderName(): string;
}


------------------------------------------
MONO CONTROLLER
------------------------------------------

$request->validate([
            'bvn' => 'required',
            'first_name' => 'required',
            'last_name' => 'required'
        ]);
        return response()->json([
            'status' => true,
            'data' => $this->service->getBvnDetails($request->all()),
            'message' => 'bvn records retrieved successfully'
        ]);