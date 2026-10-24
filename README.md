# customer-api

<?php

namespace App\Utils;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Mono
{
    private string $secretKey;
    private string $baseUrl;
    private PendingRequest $httpClient;
    private string $sessionId;

    public function __construct()
    {
        $this->secretKey = config('services.mono.secret_key');
        $this->baseUrl = config('services.mono.base_url');
        $this->httpClient = Http::withHeaders([
           'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'mono-sec-key' => $this->secretKey,
        ])->baseUrl($this->baseUrl);
    }

    public function initiateBvnVerification(string $bvn, string $scope): array
    {
        $response = $this->httpClient->post('/v2/lookup/bvn/initiate', [
            'bvn' => $bvn,
            'scope' => $scope,
        ]);
        if($response->status() !== 200 || $response->json('status') !== 'successful'){
            Log::info('Mono BVN Verification Error: ' . $response->body());
            throw new \Exception($response->json('message') ?? 'Failed to initiate BVN verification');
        }
        $responseData = $response->json('data');
        return $responseData;
    }
    public function verifyBvnOtp(string $method, string $phoneNumber, string $sessionId): array{
        $response = $this->httpClient->withHeaders([
            'x-session-id' => $sessionId,
        ])->post('/v2/lookup/bvn/verify', [
            'method' => $method,
            'phone_number' => $phoneNumber,
        ]);
        if($response->status() !== 200 || $response->json('status') !== 'successful'){
            Log::info('Mono BVN OTP Verification Error: ' . $response->body());
            throw new \Exception($response->json('message') ?? 'Failed to verify BVN OTP');
        }
        return $response->json();
    }

    public function getBvnDetails(string $otp, string $sessionId): array
    {
        $response = $this->httpClient->withHeaders([
            'x-session-id' => $sessionId,
        ])->post('/v2/lookup/bvn/details', [
            'otp' => $otp
        ]);
        if($response->status() !== 200 || $response->json('status') !== 'successful'){
            Log::info('Mono BVN Details Retrieval Error: ' . $response->body());
            throw new \Exception($response->json('message') ?? 'Failed to retrieve BVN details');
        }
        return $response->json('data');
    }
    
}

<?php

namespace App\Services;

use App\Utils\Mono;
use App\Repositories\UserRepository;

class MonoService
{

    public function __construct(private Mono $mono)
    {
    }
    public function initiateBvnVerification(string $bvn, string $scope)
    {
        try {
            $data = $this->mono->initiateBvnVerification($bvn, $scope);
            return response(['success' => true, 'data' => $data, 'message' => 'BVN verification initiated successfully'], 200);
        } catch (\Exception $e) {
            // Handle the exception as needed, e.g., log it or rethrow it
            return response(['success' => false, 'message' =>  $e->getMessage()], 400);
        }
    }

    public function verifyBvnOtp(string $method, string $phoneNumber, string $sessionId)
    {
        try {
            $data = $this->mono->verifyBvnOtp($method, $phoneNumber, $sessionId);
            return response(['success' => true, 'data' => $data, 'message' => 'OTP verified successfully'], 200);
        } catch (\Exception $e) {
            return response(['success' => false, 'message' =>  $e->getMessage()], 400);
        }
    }

    public function getBvnDetails(string $otp, string $sessionId)
    {
        try {
            $data = $this->mono->getBvnDetails($otp, $sessionId);
            return response(['success' => true, 'data' => $data, 'message' => 'BVN details retrieved successfully'], 200);
        } catch (\Exception $e) {
            return response(['success' => false, 'message' =>  $e->getMessage()], 400);
        }

    }
    
}