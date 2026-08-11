<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'user_id',
        'action',
        'description',
        'related_item_type',
        'related_item_id',
    ];

    public static function log(int $projectId, string $action, ?string $description = null, ?string $itemType = null, ?int $itemId = null): static
    {
        return static::create([
            'project_id' => $projectId,
            'user_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
            'related_item_type' => $itemType,
            'related_item_id' => $itemId,
        ]);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
