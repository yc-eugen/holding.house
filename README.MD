# Cache and Lock Services

This project provides two Symfony services: `RedisLockService` and `CacheService`. These services help manage caching and prevent race conditions using a locking mechanism.

## Installation
1. Ensure you have Symfony and the required cache component installed:
   ```sh
   composer require symfony/cache
   ```
2. Place the `RedisLockService` and `CacheService` classes in your `src/Service` directory.
3. Build and run docker dependencies
```sh
  docker compose up -d
```
4. visit url http://localhost:8080/process-huge-dataset


## Services

### RedisLockService
Manages locking mechanisms to prevent multiple processes from updating the cache simultaneously.

#### Methods:
- `acquireLock(string $lockKey, int $ttl = 10): bool` - Attempts to acquire a lock.
- `releaseLock(string $lockKey): void` - Releases the lock.

### CacheService
Handles caching operations and ensures data consistency.

#### Methods:
- `handle(array $data): array` - Retrieves the cached dataset or updates it if necessary.

## Usage
Inject `CacheService` into your controller:

```php
use App\Service\CacheService;
use Symfony\Component\HttpFoundation\JsonResponse;

class DataController
{
    private CacheService $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    public function fetchData(): JsonResponse
    {
        return $this->cacheService->getData();
    }
}
```

## Error Handling
If the cache is being updated, `CacheService` throws a `ServiceUnavailableHttpException`, prompting clients to retry later.

## License
This project is licensed under the MIT License.

