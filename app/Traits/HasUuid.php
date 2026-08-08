<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasUuid
{
    protected static function bootHasUuid(): void
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()}) && ! $model->getIncrementing()) {
                $model->{$model->getKeyName()} = Str::uuid()->toString();
            }
        });
    }
}
