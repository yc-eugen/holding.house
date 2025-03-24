<?php

declare(strict_types=1);

namespace App\Services\Redis;

use Psr\Cache\InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Contracts\Cache\CacheInterface;

class CacheService
{
    private const CACHE_ITEM_KEY = 'datasets-huge';
    private const LOCK_KEY = 'lock-datasets-huge';
    private const SET_PROCESSING_TTL = 10;
    private const CACHE_TTL = 60;

    public function __construct(
        private readonly CacheInterface $cache,
        private readonly RedisLockService $redisLockService
    ) {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function handle(array $data): array
    {
        if ($this->redisLockService->acquireLock(self::LOCK_KEY)) {
            sleep(self::SET_PROCESSING_TTL);

            $this->cache->get(self::CACHE_ITEM_KEY, function ($item) use ($data) {
                $item->expiresAfter(self::CACHE_TTL);
                return $data;
            });

            $this->redisLockService->releaseLock(self::LOCK_KEY);
        } else {
            $data = $this->cache->get(self::CACHE_ITEM_KEY, function () {
                throw new ServiceUnavailableHttpException(
                    null,
                    'Cache is being updated, please retry.'
                );
            });
        }

        return $data;
    }
}