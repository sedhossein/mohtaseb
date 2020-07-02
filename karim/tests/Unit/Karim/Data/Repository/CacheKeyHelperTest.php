<?php

namespace Tests\Unit\Karim\Data\Repository;

use App\Karim\Data\Repositories\CacheKeyHelper;
use Tests\TestCase;

class CacheKeyHelperTest extends TestCase
{
    protected $cacheHelper;

    public function setUp(): void
    {
        $this->cacheHelper = new CacheKeyHelper;
        parent::setUp();
    } // GiftCodeRepository

    public function testDuplicatesLockKey()
    {
        $code = "code";
        $id = 123;
        $expectedKey = "duplicates_lock:$id:$code";

        $this->assertEquals(
            $expectedKey,
            $this->cacheHelper->duplicatesLock($id, $code)
        );
    }

    public function testDuplicatesKey()
    {
        $code = "code";
        $expectedKey = "duplicates:$code";

        $this->assertEquals(
            $expectedKey,
            $this->cacheHelper->duplicates($code)
        );
    }

    public function testGiftCodeRemaining()
    {
        $code = "code";
        $expectedKey = "remains_gift_code:$code";

        $this->assertEquals(
            $expectedKey,
            $this->cacheHelper->giftCodeRemaining($code)
        );
    }


    public function testGiftCodeRemainingLock()
    {
        $expectedKey = "update_gift_code_remains_cache_lock";

        $this->assertEquals(
            $expectedKey,
            $this->cacheHelper->giftCodeRemainingLock()
        );
    }
}
