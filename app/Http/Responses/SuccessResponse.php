<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;

class SuccessResponse implements Responsable
{
    protected $data;
    protected $message;
    protected $status;

    public function __construct($message = 'OK', $data = null, $status = 200)
    {
        $this->data = $data;
        $this->message = $message;
        $this->status = $status;
    }

    public function toResponse($request)
    {
        $formattedData = [
            'success' => true,
            'message' => $this->message,
            'data' => $this->data,
        ];

        return new JsonResponse($formattedData, $this->status);
    }
}
