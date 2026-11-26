<?php

namespace App\ThirdParty\IdentityProviders;

use App\Exceptions\CustomException;
use App\Models\Provider;
use App\ThirdParty\IdentityProviders\Contract\ProviderContract;
use App\ThirdParty\IdentityProviders\Dto\GetBvnDetailsDto;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Mono //implements ProviderContract
{
    private PendingRequest $httpClient;

    public function __construct()
    {
        $this->httpClient = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'mono-sec-key' => config('services.mono.secret_key'),
        ])->baseUrl(config('services.mono.base_url'));
    }

    public function initiateBvnVerification(string $bvn, string $scope): array
    {
        $response = $this->httpClient->post('/v2/lookup/bvn/initiate', [
            'bvn' => $bvn,
            'scope' => $scope,
        ]);
        if ($response->status() !== 200 || $response->json('status') !== 'successful') {
            Log::info('Mono BVN Verification Error: ' . $response->body());
            throw new \Exception($response->json('message') ?? 'Failed to initiate BVN verification');
        }
        $responseData = $response->json('data');
        return $responseData;
    }
    public function verifyBvnOtp(string $method, string $phoneNumber, string $sessionId): array
    {
        $response = $this->httpClient->withHeaders([
            'x-session-id' => $sessionId,
        ])->post('/v2/lookup/bvn/verify', [
            'method' => $method,
            'phone_number' => $phoneNumber,
        ]);
        if ($response->status() !== 200 || $response->json('status') !== 'successful') {
            Log::info('Mono BVN OTP Verification Error: ' . $response->body());
            throw new \Exception($response->json('message') ?? 'Failed to verify BVN OTP');
        }
        return $response->json();
    }

    public function getBvnDetails(string ...$param)//: GetBvnDetailsDto
    {
        $response = $this->httpClient->withHeaders([
            'x-session-id' => $param['session_id'],
        ])->post('/v2/lookup/bvn/details', [
            'otp' => $param['otp']
        ]);
        if ($response->status() !== 200 || $response->json('status') !== 'successful') {
            Log::info('Mono BVN Details Retrieval Error: ' . $response->body());
            //Provider::where("name", $this->getProviderName())->increment('ttl_failed');
            throw new CustomException($response->json('message') ?? 'Failed to retrieve BVN details');
        }
        $responseData =  $response->json('data');
        
        /* Provider::where(
            "name",
            $this->getProviderName(),
        )->increment("ttl_success");
        
        return new GetBvnDetailsDto(
            bvn: $responseData['bvn'],
            first_name: $responseData['first_name'],
            last_name: $responseData['last_name'],
            provider: $this->getProviderName(),
            raw_request: json_encode($param), 
            raw_response: json_encode($responseData)
        ); */
    }
    /* public function getProviderName(): string {
        return 'mono';
    } */
    
}