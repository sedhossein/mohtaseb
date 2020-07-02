<?php

namespace App\Karim;

use App\Karim\Data\Responses\VoucherResponse;
use App\Karim\Data\Responses\VoucherWinnersResponse;
use App\Karim\Exceptions\ApplyGiftCodeFailureException;
use App\Karim\Exceptions\DuplicateGiftCodeRequestException;
use App\Karim\Exceptions\GiftCodeFinishedException;
use App\Karim\Exceptions\InvalidGiftCodeException;

interface VoucherServiceInterface
{
    /**
     * applying gift code for user
     * @throws DuplicateGiftCodeRequestException
     * @throws GiftCodeFinishedException
     * @throws ApplyGiftCodeFailureException
     * @throws InvalidGiftCodeException
     */
    public function applyGiftCodeFor(int $userID, string $code): VoucherResponse;

    public function freeGiftCode(int $userID, string $code);

    public function getGiftCodeWinners(int $page, int $pageSize): VoucherWinnersResponse;
}
