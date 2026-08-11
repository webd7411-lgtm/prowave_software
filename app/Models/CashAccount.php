<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashAccount extends Model
{
    protected $fillable = ['name', 'account_code', 'opening_balance', 'current_balance', 'description', 'is_active', 'created_by', 'updated_by'];

    protected $casts = ['is_active' => 'boolean', 'opening_balance' => 'decimal:2', 'current_balance' => 'decimal:2'];

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function incomes(): HasMany
    {
        return $this->hasMany(IncomeRecord::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(FinancialTransaction::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function totalIncome(): float
    {
        return (float) $this->transactions()->sum('credit');
    }

    public function totalExpense(): float
    {
        return (float) $this->transactions()->sum('debit');
    }
}
