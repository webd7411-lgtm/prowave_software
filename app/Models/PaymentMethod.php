<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = ['name', 'slug', 'icon', 'is_active', 'is_system'];

    protected $casts = ['is_active' => 'boolean', 'is_system' => 'boolean'];

    public static function defaults(): array
    {
        return [
            ['name' => 'Cash', 'slug' => 'cash', 'icon' => 'bi-cash-coin', 'is_system' => true],
            ['name' => 'Bank Transfer', 'slug' => 'bank-transfer', 'icon' => 'bi-bank', 'is_system' => true],
            ['name' => 'JazzCash', 'slug' => 'jazzcash', 'icon' => 'bi-phone', 'is_system' => true],
            ['name' => 'EasyPaisa', 'slug' => 'easypaisa', 'icon' => 'bi-phone-fill', 'is_system' => true],
            ['name' => 'Card', 'slug' => 'card', 'icon' => 'bi-credit-card', 'is_system' => true],
            ['name' => 'Cheque', 'slug' => 'cheque', 'icon' => 'bi-file-earmark-text', 'is_system' => true],
            ['name' => 'Online Payment', 'slug' => 'online-payment', 'icon' => 'bi-globe', 'is_system' => true],
            ['name' => 'Other', 'slug' => 'other', 'icon' => 'bi-three-dots', 'is_system' => true],
        ];
    }
}
