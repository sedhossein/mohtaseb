<?php

namespace App\Karim\Data\Responses;

class VoucherWinnersResponse
{
    /* @var int $count */
    private $count;

    /* @var array<string> $list */
    private $list;

    public function getList(): array
    {
        return $this->list;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function withList(array $list): self
    {
        $this->list = $list;
        return $this;
    }

    public function withCount(int $count): self
    {
        $this->count = $count;
        return $this;
    }
}
