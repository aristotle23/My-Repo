<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;

class CustomException extends Exception
{
    public function __construct(
        string $message,
        int $code = 400,
        public array $data = [],
    ) {
        parent::__construct($message, $code);
    }
    public function render(Request $request)
    {
        $responseData = [
            "success" => false,
            "statusCode" => $this->getCode(),
            "data" => $this->data,
            "message" => $this->getMessage(),
        ];

        return response()->json($responseData, $this->getCode());
    }
}
