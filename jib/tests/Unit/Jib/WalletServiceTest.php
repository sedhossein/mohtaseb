<?php

namespace Tests\Unit\Jib;

use App\Jib\Contracts\UserRepositoryContract;
use App\Jib\Data\Models\Wallet;
use App\Jib\WalletService;
use App\Utils\Enums\TransactionTypeEnum;
use Mockery\MockInterface;
use Tests\TestCase;

class WalletServiceTest extends TestCase
{
    public function testUserCreditorCallsRepositoryWithRightValues()
    {
        $repositoryMock = \Mockery::mock(UserRepositoryContract::class, function (MockInterface $mock) {
            $mock
                ->shouldReceive('userCreditor')
                ->once()
                ->with(123, TransactionTypeEnum::GiftCode, 1000, 'a Code')
                ->andReturn('SOME-REF-CODE');
        });

        $service = new WalletService($repositoryMock);

        $refId = $service
            ->userCreditor(123, TransactionTypeEnum::GiftCode, 1000, 'a Code');

        $this->assertEquals('SOME-REF-CODE', $refId);
    }

    public function testGetUserWalletReturnsWalletFromRepository()
    {
        $repositoryMock = \Mockery::mock(UserRepositoryContract::class, function (MockInterface $mock) {
            $mock
                ->shouldReceive('getWallet')
                ->once()
                ->with(123)
                ->andReturn(
                    (new Wallet())
                        ->withBalance(1000)
                );
        });

        $service = new WalletService($repositoryMock);

        $wallet = $service
            ->getUserWallet(123);

        $this->assertEquals(1000, $wallet->getWallet()->getBalance());
    }
}
