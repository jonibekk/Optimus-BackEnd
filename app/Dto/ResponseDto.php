<?php

namespace App\Dto;

class ResponseDto
{
    /**
     * @var bool
     */
    public $success;

    /**
     * @var array<mixed>
     */
    public $data;

    /**
     * @var string
     */
    public $message;

    public function toArray()
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'body' => $this->data
        ];
    }
}