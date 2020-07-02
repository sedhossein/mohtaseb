<?php

namespace App\Services\Wallet;

use App\Services\Wallet\Jib\Exceptions\JibServiceException;

interface WalletServiceInterface
{
    /**
     * Adds credit to user balance
     * @throws JibServiceException
     */
    public function userCreditor(
        int $userID,
        int $transactionType,
        int $amount,
        string $description
    );

    /**
     * deduct credit from user balance
     */
    public function userDebtor(
        int $id,
        int $transactionType,
        int $relationId,
        int $amount,
        string $description = null
    );

    /**
     * returns the current balance of the given user ID
     * @throws JibServiceException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getUserBalance(int $id): int;

    /**
     * returns a unique transaction reference id
     */
    public function generateUniqueReferenceId(): string;
}
