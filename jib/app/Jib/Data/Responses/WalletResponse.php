<?php

namespace App\Jib\Data\Responses;

use App\Jib\Data\Models\Wallet;

class WalletResponse
{
    private $wallet;

    public function getWallet(): Wallet
    {
        return $this->wallet;
    }

    public function withWallet(Wallet $wallet): self
    {
        $this->wallet = $wallet;
        return $this;
    }
}
