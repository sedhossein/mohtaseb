<?php

namespace App\Karim\Data\Repositories;

use App\Karim\Contracts\StatisticsRepositoryContract;
use Illuminate\Database\Query\Builder;

class StatisticsRepository implements StatisticsRepositoryContract
{
    private $winnersTableQueryBuilder;

    public function __construct(Builder $winnersTableQueryBuilder)
    {
        $this->winnersTableQueryBuilder = $winnersTableQueryBuilder;
    }

    /**
     * @inheritDoc
     */
    public function getWinnersCount(): int
    {
        return $this->winnersTableQueryBuilder->count();
    }

    /**
     * @inheritDoc
     */
    public function getWinnersList(int $page, int $pageSize): array
    {
        $winners = $this->winnersTableQueryBuilder
            ->select(['user_id'])
            ->take($pageSize)
            ->skip(($page-1) * $pageSize)
            ->get();

        return $winners->toArray() ?? [];
    }
}
