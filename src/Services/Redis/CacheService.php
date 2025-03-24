<?php

declare(strict_types=1);

namespace App\Services\Redis;

use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Contracts\Cache\CacheInterface;

readonly class CacheService
{
    private const CACHE_ITEM_KEY = 'datasets-huge';
    private const LOCK_KEY = 'lock-datasets-huge';
    private const SET_PROCESSING_TTL = 10;
    private const CACHE_TTL = 60;

    public function __construct(
        private CacheInterface $cache,
        private RedisLockService $redisLockService
    ) {
    }

    public function handle(array $data): array
    {
        if ($this->redisLockService->acquireLock(self::LOCK_KEY)) {
            sleep(self::SET_PROCESSING_TTL);
            $cacheItem = $this->cache->getItem(self::CACHE_ITEM_KEY);
            $cacheItem->set($data);
            $cacheItem->expiresAfter(self::CACHE_TTL);
            $this->cache->save($cacheItem);

            $this->redisLockService->releaseLock(self::LOCK_KEY);
        } else {
            $cacheItem = $this->cache->getItem(self::CACHE_ITEM_KEY);
            if (!$cacheItem->isHit()) {
                throw new ServiceUnavailableHttpException(
                    null,
                    'Cache is being updated, please retry.'
                );
            }
            $data = $cacheItem->get();
        }

        return $data;
    }
}