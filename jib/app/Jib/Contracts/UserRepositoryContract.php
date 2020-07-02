<?php

namespace App\Jib\Contracts;

use App\Jib\Data\Models\Wallet;
use App\Jib\Exceptions\WalletNotFoundException;
use Exception;

interface UserRepositoryContract
{
    /**
     * @throws WalletNotFoundException
     */
    public function getWallet(int $id): Wallet;

    /**
     * @throws Exception
     */
    public function userCreditor(
        int $userID,
        int $transactionType,
        int $amount,
        string $description
    ): string;
}
