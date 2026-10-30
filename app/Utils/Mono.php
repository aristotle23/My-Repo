<?php

namespace App\Utils;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Mono
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