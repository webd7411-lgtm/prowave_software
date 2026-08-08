<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    public const CACHE_KEY = 'app.settings';

    public $timestamps = true;

    protected $fillable = [
        'group',
        'key',
        'value',
        'type',
    ];

    protected $casts = [
        'value' => 'json',
    ];

    public function scopeGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    public function scopeKey($query, string $key)
    {
        return $query->where('key', $key);
    }

    public static function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public static function allCached(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            return self::query()
                ->select('group', 'key', 'value', 'type')
                ->get()
                ->groupBy('group')
                ->map(fn ($items) => $items->mapWithKeys(fn ($item) => [$item->key => $item->value])->all())
                ->all();
        });
    }
}
