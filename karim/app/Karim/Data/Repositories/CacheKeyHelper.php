<?php


namespace App\Karim\Data\Repositories;


class CacheKeyHelper
{
    public function duplicatesLock(int $id, string $code): string
    {
        return "duplicates_lock:$id:$code";
    }

    public function duplicates(string $code): string
    {
        return "duplicates:$code";
    }

    public function giftCodeRemaining(string $code): string
    {
        return "remains_gift_code:$code";
    }

    public function giftCodeRemainingLock(): string
    {
        return "update_gift_code_remains_cache_lock";
    }
}
