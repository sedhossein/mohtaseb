<?php

namespace App\Karim;

use App\Karim\Contracts\GiftCodeRepositoryContract;
use App\Karim\Contracts\StatisticsRepositoryContract;
use App\Karim\Data\Decorators\CellphoneDecorator;
use App\Karim\Data\Responses\VoucherResponse;
use App\Karim\Data\Responses\VoucherWinnersResponse;
use App\Karim\Exceptions\DuplicateGiftCodeRequestException;
use App\Karim\Exceptions\GiftCodeFinishedException;

class VoucherService implements VoucherServiceInterface
{
    private $giftCodeRepository;

    private $statisticsRepository;

    private $decorator;

    public function __construct(
        GiftCodeRepositoryContract $giftCodeRepository,
        StatisticsRepositoryContract $statisticsRepository,
        CellphoneDecorator $decorator)
    {
        $this->giftCodeRepository = $giftCodeRepository;
        $this->statisticsRepository = $statisticsRepository;
        $this->decorator = $decorator;
    }

    /**
     * @inheritDoc
     */
    public function applyGiftCodeFor(int $userID, string $code): VoucherResponse
    {
        if ($this->giftCodeRepository->isDuplicateRequest($userID, $code)) {
            throw new DuplicateGiftCodeRequestException;
        }

        if (!$this->giftCodeRepository->isGiftCodeRemaining($code)) {
            throw new GiftCodeFinishedException;
        }

        $this->giftCodeRepository->applyGiftCode($userID, $code);

        $giftCode = $this->giftCodeRepository->getGiftCode($code);

        return (new VoucherResponse)
            ->withAmount($giftCode['value'])
            ->withDescription($giftCode['description']);
    }

    public function freeGiftCode(int $userID, string $code)
    {
        $this->giftCodeRepository->revertGiftCode($userID, $code);
    }

    /**
     * @inheritDoc
     */
    public function getGiftCodeWinners(int $page, int $pageSize): VoucherWinnersResponse
    {
        $winnersCount = $this->statisticsRepository->getWinnersCount();
        $winnersList = $this->statisticsRepository->getWinnersList($page, $pageSize);

        $this->decorator->setCellphones($winnersList);

        return (new VoucherWinnersResponse)
            ->withCount($winnersCount)
            ->withList($this->decorator->decorate());
    }
}
