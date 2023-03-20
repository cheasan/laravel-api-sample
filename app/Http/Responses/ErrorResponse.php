<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;

class ErrorResponse implements Responsable
{
    protected $errors;
    protected $message;
    protected $status;

    public function __construct($errors = null, $status = 400)
    {
        $this->errors = $errors;
        $this->message = 'Expected faulty. Please check the error message below.';
        $this->status = $status;
    }

    public function toResponse($request)
    {
        $formattedData = [
            'success' => false,
            'message' => $this->message,
            'errors' => $this->errors,
        ];

        return new JsonResponse($formattedData, $this->status);
    }
}
