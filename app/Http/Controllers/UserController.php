<?php
namespace App\Http\Controllers;

use App\Services\UserServices;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class UserController extends Controller
{

   

    public function __construct(private UserServices $service)
    {
        
    }

    public function getUser(Request $request)
    {
        
        return $this->service->getUser();
    }
    
}
