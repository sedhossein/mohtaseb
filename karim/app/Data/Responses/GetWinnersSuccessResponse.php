<?php

namespace App\Data\Responses;

use JsonSerializable;

class GetWinnersSuccessResponse implements JsonSerializable
{
    private $message;
    private $count;
    private $winners;

    public function __construct(string $message, int $count, array $winners)
    {
        $this->message = $message;
        $this->count = $count;
        $this->winners = $winners;
    }

    public function jsonSerialize()
    {
        return [
            'success' => true,
            'message' => $this->message,
            'data' => [
                'count' => $this->count,
                'winners' => collect($this->winners)->map(function ($cellphone) {
                    return ["masked_number" => $cellphone];
                }),
            ],
        ];
    }
}
