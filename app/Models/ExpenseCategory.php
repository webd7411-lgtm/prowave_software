<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpenseCategory extends Model
{
    protected $fillable = ['name', 'slug', 'color', 'icon', 'description', 'is_active', 'is_system', 'created_by'];

    protected $casts = ['is_active' => 'boolean', 'is_system' => 'boolean'];

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'category_id');
    }

    public static function defaultCategories(): array
    {
        return [
            ['name' => 'Office Rent', 'slug' => 'office-rent', 'color' => '#dc3545', 'icon' => 'bi-building', 'is_system' => true],
            ['name' => 'Electricity', 'slug' => 'electricity', 'color' => '#ffc107', 'icon' => 'bi-lightning-charge', 'is_system' => true],
            ['name' => 'Internet', 'slug' => 'internet', 'color' => '#0dcaf0', 'icon' => 'bi-wifi', 'is_system' => true],
            ['name' => 'Telephone', 'slug' => 'telephone', 'color' => '#6f42c1', 'icon' => 'bi-telephone', 'is_system' => true],
            ['name' => 'Office Supplies', 'slug' => 'office-supplies', 'color' => '#fd7e14', 'icon' => 'bi-printer', 'is_system' => true],
            ['name' => 'Software/Subscriptions', 'slug' => 'software-subscriptions', 'color' => '#20c997', 'icon' => 'bi-cloud-download', 'is_system' => true],
            ['name' => 'Hardware', 'slug' => 'hardware', 'color' => '#6c757d', 'icon' => 'bi-cpu', 'is_system' => true],
            ['name' => 'Transport', 'slug' => 'transport', 'color' => '#0d6efd', 'icon' => 'bi-car-front', 'is_system' => true],
            ['name' => 'Fuel', 'slug' => 'fuel', 'color' => '#e83e8c', 'icon' => 'bi-fuel-pump', 'is_system' => true],
            ['name' => 'Maintenance', 'slug' => 'maintenance', 'color' => '#795548', 'icon' => 'bi-tools', 'is_system' => true],
            ['name' => 'Marketing', 'slug' => 'marketing', 'color' => '#ff5722', 'icon' => 'bi-megaphone', 'is_system' => true],
            ['name' => 'Salary', 'slug' => 'salary', 'color' => '#4caf50', 'icon' => 'bi-wallet2', 'is_system' => true],
            ['name' => 'Travel', 'slug' => 'travel', 'color' => '#2196f3', 'icon' => 'bi-airplane', 'is_system' => true],
            ['name' => 'Project Expense', 'slug' => 'project-expense', 'color' => '#9c27b0', 'icon' => 'bi-kanban', 'is_system' => true],
            ['name' => 'Miscellaneous', 'slug' => 'miscellaneous', 'color' => '#9e9e9e', 'icon' => 'bi-three-dots', 'is_system' => true],
        ];
    }
}
