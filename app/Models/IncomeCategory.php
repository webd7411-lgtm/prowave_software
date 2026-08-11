<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IncomeCategory extends Model
{
    protected $fillable = ['name', 'slug', 'color', 'icon', 'description', 'is_active', 'is_system', 'created_by'];

    protected $casts = ['is_active' => 'boolean', 'is_system' => 'boolean'];

    public function incomes(): HasMany
    {
        return $this->hasMany(IncomeRecord::class, 'category_id');
    }

    public static function defaultCategories(): array
    {
        return [
            ['name' => 'Client Payment', 'slug' => 'client-payment', 'color' => '#198754', 'icon' => 'bi-person-check', 'is_system' => true],
            ['name' => 'Project Payment', 'slug' => 'project-payment', 'color' => '#0d6efd', 'icon' => 'bi-kanban', 'is_system' => true],
            ['name' => 'Service Income', 'slug' => 'service-income', 'color' => '#20c997', 'icon' => 'bi-gear', 'is_system' => true],
            ['name' => 'Other Income', 'slug' => 'other-income', 'color' => '#6c757d', 'icon' => 'bi-three-dots', 'is_system' => true],
        ];
    }
}
