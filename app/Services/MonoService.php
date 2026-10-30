<?php

namespace App\Services;

use App\Utils\Mono;

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