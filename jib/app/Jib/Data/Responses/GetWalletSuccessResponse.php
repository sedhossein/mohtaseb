<?php

namespace App\Jib\Data\Responses;

use App\Jib\Data\Models\Wallet;
use JsonSerializable;

class GetWalletSuccessResponse implements JsonSerializable
{
    private $wallet;

    public function __construct(Wallet $wallet)
    {
        $this->wallet = $wallet;
    }

    public function jsonSerialize()
    {
        return [
            'success' => true,
            'messages' => "",
            'data' => [
                'wallet' => $this->wallet
            ],
        ];
    }
}
