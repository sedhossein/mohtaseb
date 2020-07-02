<?php

namespace App\Jib\Data\Models;

class Wallet implements \JsonSerializable
{
    private $balance;

    public function getBalance(): int
    {
        return $this->balance;
    }

    public function withBalance(int $balance): self
    {
        $this->balance = $balance;
        return $this;
    }

    public function jsonSerialize()
    {
        return [
            'balance' => $this->getBalance(),
        ];
    }
}
