<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

trait HasCacheable
{
    public static function cacheKey(string $suffix = ''): string
    {
        $key = str(class_basename(static::class))->slug();

        return $suffix ? "{$key}.{$suffix}" : $key;
    }

    public static function cachedList(string $cacheKey = '', int $ttl = 3600, array $where = []): mixed
    {
        $cacheKey = $cacheKey ?: static::cacheKey('list');

        return Cache::remember($cacheKey, $ttl, function () use ($where) {
            $query = static::query();

            foreach ($where as $column => $value) {
                $query->where($column, $value);
            }

            return $query->get();
        });
    }

    public static function clearModelCache(string $key = ''): void
    {
        if (property_exists(static::class, 'cacheableKeys')) {
            foreach (static::$cacheableKeys as $suffix) {
                Cache::forget(static::cacheKey($suffix));
            }
        }

        if ($key) {
            Cache::forget($key);
        }
    }
}
