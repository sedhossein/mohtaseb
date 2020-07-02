<?php

namespace App\Karim\Contracts;

use App\Karim\Exceptions\ApplyGiftCodeFailureException;
use App\Karim\Exceptions\DuplicateGiftCodeRequestException;
use App\Karim\Exceptions\GiftCodeFinishedException;
use App\Karim\Exceptions\InvalidGiftCodeException;

interface GiftCodeRepositoryContract
{
    public function isDuplicateRequest(int $id, string $code): bool;

    /**
     * @throws InvalidGiftCodeException
     */
    public function isGiftCodeRemaining(string $code): bool;

    /**
     * @throws ApplyGiftCodeFailureException
     * @throws GiftCodeFinishedException
     * @throws InvalidGiftCodeException
     * @throws DuplicateGiftCodeRequestException
     */
    public function applyGiftCode(int $id, string $code);

    /**
     * @throws InvalidGiftCodeException
     */
    public function getGiftCode(string $code): array;

    /**
     * @throws InvalidGiftCodeException
     */
    public function revertGiftCode(int $id, string $code): void;
}
