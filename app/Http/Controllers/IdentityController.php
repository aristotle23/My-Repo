<?php
namespace App\Http\Controllers;

use App\Services\IdentityService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\IdentityService;


class IdentityController extends Controller
{

   

    public function __construct(private IdentityService $service)
    {
        
    }

    public function getBvnDetails(Request $request): JsonResponse{
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
    }
    
}
