<?php

namespace Tests\Unit\Jib\Data\Repositories;

use App\Jib\Data\Repositories\UserTransactionRepository;
use Illuminate\Database\Query\Builder;
use Mockery\MockInterface;
use Tests\TestCase;

class UserTransactionRepositoryTest extends TestCase
{
    public function testGetWalletIssuesRightQuery()
    {
        $builderMock = \Mockery::mock(Builder::class, function (MockInterface $mock) {
            $mock
                ->shouldReceive('select')->with(['current_balance'])->once()->andReturnSelf()
                ->shouldReceive('where')->with('user_id', 123)->once()->andReturnSelf()
                ->shouldReceive('latest')->with('id')->once()->andReturnSelf()
                ->shouldReceive('first')->once()->andReturn(
                    json_decode(json_encode(['current_balance' => 1500]))
                );
        });

        $repository = new UserTransactionRepository($builderMock);

        $wallet = $repository->getWallet(123);

        $this->assertEquals(1500, $wallet->getBalance());
    }
}
