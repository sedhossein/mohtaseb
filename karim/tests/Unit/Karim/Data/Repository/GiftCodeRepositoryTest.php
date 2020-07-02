<?php

namespace Tests\Unit\Karim\Data\Repository;

use App\Karim\Data\Repositories\CacheKeyHelper;
use App\Karim\Data\Repositories\GiftCodeRepository;
use Illuminate\Database\Query\Builder;
use Mockery\MockInterface;
use Redis;
use Tests\TestCase;

class GiftCodeRepositoryTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function testIsDuplicateRequestReturnsIsTrue()
    {
        $id = 123;
        $code = "code";
        $expectedResult = true;
        $redis = \Mockery::mock(Redis::class, function (MockInterface $mock) {
            $mock->shouldReceive('get')
                ->with('duplicates_lock:123:code')
                ->once()
                ->andReturn(true);
        });

        $res = (new GiftCodeRepository(
            \Mockery::mock(Builder::class),
            \Mockery::mock(Builder::class),
            new CacheKeyHelper,
            $redis,
            0,
            0,
            0
        ))->isDuplicateRequest($id, $code);

        $this->assertEquals($expectedResult, $res);
    }
}
