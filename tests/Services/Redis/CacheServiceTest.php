<?php

declare(strict_types=1);

namespace App\Tests\Services\Redis;

use App\Services\Redis\CacheService;
use App\Services\Redis\RedisLockService;
use PHPUnit\Framework\TestCase;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Contracts\Cache\CacheInterface;

class CacheServiceTest extends TestCase
{
    private CacheInterface $cache;
    private RedisLockService $redisLockService;
    private CacheService $cacheService;

    protected function setUp(): void
    {
        $this->cache = $this->createMock(CacheInterface::class);
        $this->redisLockService = $this->createMock(RedisLockService::class);
        $this->cacheService = new CacheService($this->cache, $this->redisLockService);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testHandleWhenLockAcquired(): void
    {
        $data = ['key' => 'value'];

        $this->redisLockService->expects($this->once())
            ->method('acquireLock')
            ->with('lock-datasets-huge')
            ->willReturn(true);

        $this->cache->expects($this->once())
            ->method('get')
            ->with('datasets-huge')
            ->willReturn($data);

        $this->redisLockService->expects($this->once())
            ->method('releaseLock')
            ->with('lock-datasets-huge');

        $result = $this->cacheService->handle($data);
        $this->assertSame($data, $result);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testHandleWhenLockNotAcquiredAndCacheExists(): void
    {
        $data = ['cached_key' => 'cached_value'];

        $this->redisLockService->expects($this->once())
            ->method('acquireLock')
            ->with('lock-datasets-huge')
            ->willReturn(false);

        $this->cache->expects($this->once())
            ->method('get')
            ->with('datasets-huge')
            ->willReturn($data);

        $result = $this->cacheService->handle(['new_key' => 'new_value']);
        $this->assertSame($data, $result);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testHandleWhenLockNotAcquiredAndCacheIsEmpty(): void
    {
        $this->expectException(ServiceUnavailableHttpException::class);
        $this->expectExceptionMessage('Cache is being updated, please retry.');

        $this->redisLockService->expects($this->once())
            ->method('acquireLock')
            ->with('lock-datasets-huge')
            ->willReturn(false);

        $this->cache->expects($this->once())
            ->method('get')
            ->with('datasets-huge')
            ->willThrowException(new ServiceUnavailableHttpException(
                null,
                'Cache is being updated, please retry.'
            ));

        $this->cacheService->handle(['new_key' => 'new_value']);
    }
}