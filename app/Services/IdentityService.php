<?php
namespace App\Services;

use App\Exceptions\CustomException;
use App\Models\BvnLookupLog;
use App\ThirdParty\IdentityProviders\Contract\ProviderContract;
use Illuminate\Support\Facades\Crypt;

class IdentityService {
    public function __construct(public ProviderContract $identityProvider)
    {
    }
    public function getBvnDetails(array $request): string{
        $dbRecord = BvnLookupLog::getBvnRecord($request["bvn"]);
        if ( $dbRecord ) return $dbRecord;
        
        $providerRecord = $this->identityProvider->getBvnDetails(...$request);
        
        if (  !$providerRecord || strtolower($providerRecord->first_name) != strtolower($request["first_name"]) &&
                strtolower($providerRecord->last_name) != strtolower($request["last_name"])
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