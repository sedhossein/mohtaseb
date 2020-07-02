<?php

namespace Tests\Unit\Karim;

use App\Karim\Contracts\GiftCodeRepositoryContract;
use App\Karim\Contracts\StatisticsRepositoryContract;
use App\Karim\Data\Decorators\CellphoneDecorator;
use App\Karim\Data\Responses\VoucherResponse;
use App\Karim\Exceptions\DuplicateGiftCodeRequestException;
use App\Karim\Exceptions\GiftCodeFinishedException;
use App\Karim\VoucherService;
use Mockery\MockInterface;
use Tests\TestCase;

class VoucherServiceTest extends TestCase
{
    public function testVoucherServiceDuplicateGiftCodeRequestException()
    {
        $this->expectException(DuplicateGiftCodeRequestException::class);

        $userID = 123;
        $code = 'a Code';
        $giftCodeRepositoryMock = \Mockery::mock(GiftCodeRepositoryContract::class,
            function (MockInterface $mock) use ($code, $userID) {
                $mock->shouldReceive('isDuplicateRequest')
                    ->once()
                    ->with($userID, $code)
                    ->andReturn(true);
            });
        $statisticsRepositoryMock = \Mockery::mock(StatisticsRepositoryContract::class,
            function (MockInterface $mock) {
            });

        $voucherService = new VoucherService(
            $giftCodeRepositoryMock,
            $statisticsRepositoryMock,
            new CellphoneDecorator,
        );

        $voucherService->applyGiftCodeFor($userID, $code);
    }

    public function testVoucherServiceGiftCodeFinishedException()
    {
        $this->expectException(GiftCodeFinishedException::class);

        $userID = 123;
        $code = 'a Code';
        $giftCodeRepositoryMock = \Mockery::mock(GiftCodeRepositoryContract::class,
            function (MockInterface $mock) use ($code, $userID) {
                $mock->shouldReceive('isDuplicateRequest')
                    ->once()
                    ->with($userID, $code)
                    ->andReturn(false);

                $mock->shouldReceive('isGiftCodeRemaining')
                    ->once()
                    ->with($code)
                    ->andReturn(false);
            });
        $statisticsRepositoryMock = \Mockery::mock(StatisticsRepositoryContract::class,
            function (MockInterface $mock) {
            });

        $voucherService = new VoucherService(
            $giftCodeRepositoryMock,
            $statisticsRepositoryMock,
            new CellphoneDecorator,
        );

        $voucherService->applyGiftCodeFor($userID, $code);
    }

    public function testVoucherServiceWorksFine()
    {
        $expectedResponse = (new VoucherResponse)
            ->withAmount(1000)
            ->withDescription("code description");
        $userID = 123;
        $code = 'a Code';
        $giftCodeRepositoryMock = \Mockery::mock(GiftCodeRepositoryContract::class,
            function (MockInterface $mock) use ($code, $userID, $expectedResponse) {
                $mock->shouldReceive('isDuplicateRequest')
                    ->once()
                    ->with($userID, $code)
                    ->andReturn(false);

                $mock->shouldReceive('isGiftCodeRemaining')
                    ->once()
                    ->with($code)
                    ->andReturn(true);

                $mock->shouldReceive('applyGiftCode')
                    ->once()
                    ->with($userID, $code);

                $mock->shouldReceive('getGiftCode')
                    ->once()
                    ->with($code)
                    ->andReturn([
                        'value' => $expectedResponse->getAmount(),
                        'description' => $expectedResponse->getDescription(),
                    ]);
            });

        $statisticsRepositoryMock = \Mockery::mock(StatisticsRepositoryContract::class,
            function (MockInterface $mock) {
            });

        $voucherService = new VoucherService(
            $giftCodeRepositoryMock,
            $statisticsRepositoryMock,
            new CellphoneDecorator,
        );

        $voucherResponse = $voucherService->applyGiftCodeFor($userID, $code);

        $this->assertEquals($expectedResponse->getAmount(), $voucherResponse->getAmount());
        $this->assertEquals($expectedResponse->getDescription(), $voucherResponse->getDescription());
    }
}
