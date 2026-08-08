<?php

namespace App\Services;

use App\Models\Setting;

class SettingService
{
    protected array $cache = [];

    public function get(?string $key = null, mixed $default = null): mixed
    {
        if (! $key) {
            return $this->all();
        }

        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        [$group, $settingKey] = $this->parseKey($key);

        $value = Setting::allCached()[$group][$settingKey] ?? $default;

        return $this->cache[$key] = $value;
    }

    public function all(): array
    {
        return Setting::allCached();
    }

    public function set(string $key, mixed $value, string $type = 'string'): Setting
    {
        [$group, $settingKey] = $this->parseKey($key);

        return Setting::updateOrCreate(
            ['group' => $group, 'key' => $settingKey],
            ['value' => $value, 'type' => $type]
        );
    }

    public function setMany(array $settings): void
    {
        foreach ($settings as $key => $value) {
            $this->set($key, $value);
        }
    }

    public function forget(string $key): void
    {
        [$group, $settingKey] = $this->parseKey($key);

        Setting::query()->group($group)->key($settingKey)->delete();
    }

    public function group(string $group): array
    {
        return Setting::allCached()[$group] ?? [];
    }

    protected function parseKey(string $key): array
    {
        $parts = explode('.', $key, 2);

        return [($parts[0] ?? 'general'), ($parts[1] ?? $key)];
    }
}
