<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use Symfony\Component\HttpFoundation\Response;

class ErrorResponse implements Responsable
{
    protected $errors;
    protected $message;
    protected $status;

    public function __construct($errors = null, $message = 'KO', $status = 400)
    {
        $this->errors = $errors;
        $this->message = $message;
        $this->status = $status;
    }

    public function toResponse($request)
    {
        $formattedData = [
            'success' => false,
            'message' => $this->message,
            'errors' => $this->errors,
        ];

        return new Response(json_encode($formattedData), $this->status);
    }
}
