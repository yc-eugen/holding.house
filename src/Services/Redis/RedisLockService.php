<?php

declare(strict_types=1);

namespace App\Services\Redis;

use Symfony\Contracts\Cache\CacheInterface;

readonly class RedisLockService
{
    public function __construct(
        private CacheInterface $cache
    ) {
    }

    public function acquireLock(string $lockKey, int $ttl = 10): bool
    {
        $lock = $this->cache->getItem($lockKey);

        if (!$lock->isHit()) {
            $lock->set(true);
            $lock->expiresAfter($ttl);
            $this->cache->save($lock);

            return true;
        }

        return false;
    }

    public function releaseLock(string $lockKey): void
    {
        $this->cache->deleteItem($lockKey);
    }
}