<?php

namespace App\Jib;

use App\Jib\Data\Responses\WalletResponse;
use App\Jib\Exceptions\WalletServiceException;

interface WalletServiceInterface
{
    /**
     * Adds credit to user balance
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    public function userCreditor(
        int $userID,
        int $transactionType,
        int $amount,
        string $description
    ): string;

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
     * @throws WalletServiceException
     * @throws Exceptions\WalletNotFoundException
     */
    public function getUserWallet(int $id): WalletResponse;
}
