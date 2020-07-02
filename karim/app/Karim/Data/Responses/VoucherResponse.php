<?php

namespace App\Karim\Data\Responses;

class VoucherResponse
{
    private $description;
    private $amount;

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function withAmount(int $amount): self
    {
        $this->amount = $amount;
        return $this;
    }

    public function withDescription(string $text): self
    {
        $this->description = $text;
        return $this;
    }
}
