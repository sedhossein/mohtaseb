<?php

namespace App\Jib;

use App\Jib\Contracts\UserRepositoryContract;
use App\Jib\Data\Responses\WalletResponse;

class WalletService implements WalletServiceInterface
{
    private $userTransactionRepository;

    public function __construct(UserRepositoryContract $userTransactionRepository)
    {
        $this->userTransactionRepository = $userTransactionRepository;
    }

    /**
     * @inheritDoc
     */
    public function userCreditor(
        int $userID,
        int $transactionType,
        int $amount,
        string $description): string
    {
        $referenceID = $this->userTransactionRepository->userCreditor(
            $userID,
            $transactionType,
            $amount,
            $description
        );

        return $referenceID;
    }

    /**
     * @inheritDoc
     */
    public function getUserWallet(int $id): WalletResponse
    {
        return (new WalletResponse)->withWallet(
            $this->userTransactionRepository->getWallet($id)
        );
    }

    public function userDebtor(
        int $id,
        int $transactionType,
        int $relationId,
        int $amount,
        string $description = null)
    {
        // TODO: Implement userDebtor() method.
    }
}
