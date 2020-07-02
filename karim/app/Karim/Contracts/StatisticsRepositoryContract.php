<?php

namespace App\Karim\Contracts;

interface StatisticsRepositoryContract
{
    public function getWinnersCount(): int;

    /**
     * @return array<string>
     */
    public function getWinnersList(int $page, int $pageSize): array;
}
