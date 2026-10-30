<?php
namespace App\Http\Controllers;

use App\Services\UserServices;
use App\Http\Controllers\Controller;
use App\Services\MonoService;
use Illuminate\Http\Request;


class MonoController extends Controller
{

   

    public function __construct(private MonoService $service)
    {
        
    }

    public function initiateBvn(Request $request){
        $request->validate([
            'bvn' => 'required',
            'scope' => 'required'
        ]);
        return $this->service->initiateBvnVerification($request->bvn, $request->scope);
    }
    public function verifyBvnOtp(Request $request){
        $request->validate([
            'method' => 'required',
            'phone_number' => 'required',
            'session_id' => 'required'
        ]);
        return $this->service->verifyBvnOtp($request->method, $request->phone_number, $request->session_id);
    }
    public function getBvnDetails(Request $request){
        $request->validate([
            'otp' => 'required',
            'session_id' => 'required'
        ]);
        return $this->service->getBvnDetails($request->otp, $request->session_id);
    }
}