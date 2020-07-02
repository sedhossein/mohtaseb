<?php

namespace App\Karim\Data\Responses;

use JsonSerializable;

class FailureResponse implements JsonSerializable
{
    private $message;

    public function __construct(string $message)
    {
        $this->message = $message;
    }

    public function jsonSerialize(): array
    {
        return [
            "success" => false,
            "message" => $this->message,
        ];
    }
}
