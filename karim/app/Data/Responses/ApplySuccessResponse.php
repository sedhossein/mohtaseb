<?php

namespace App\Data\Responses;

use JsonSerializable;

class ApplySuccessResponse implements JsonSerializable
{
    private $message;
    private $amount;
    private $description;

    public function __construct(string $message, int $amount, string $description)
    {
        $this->message = $message;
        $this->amount = $amount;
        $this->description = $description;
    }

    public function jsonSerialize()
    {
        return [
            'success' => true,
            'message' => $this->message,
            'data' => [
                'gift_code' => [
                    'amount' => $this->amount,
                    'description' => $this->description,
                ],
            ],
        ];
    }
}
