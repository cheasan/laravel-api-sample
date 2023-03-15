<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use Symfony\Component\HttpFoundation\Response;

class SuccessResponse implements Responsable
{
    protected $data;
    protected $message;
    protected $status;

    public function __construct($data = null, $message = 'OK', $status = 200)
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

        return new Response(json_encode($formattedData), $this->status);
    }
}
