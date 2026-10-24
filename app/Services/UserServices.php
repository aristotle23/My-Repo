<?php

namespace App\Services;


use App\Models\User;


class UserServices
{
    protected $success = false;

    public function getUser()
    {
       
        return [
            'name' => 'Tom doe',
        ];
    }

}
