<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankAccount extends Model
{
    protected $fillable = [
        'bank_name', 'account_title', 'account_number', 'iban', 'branch', 'swift_code',
        'opening_balance', 'current_balance', 'currency', 'status', 'notes',
        'created_by', 'updated_by',
    ];

    protected $casts = ['opening_balance' => 'decimal:2', 'current_balance' => 'decimal:2'];

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

    public function maskedAccountNumber(): string
    {
        if (!$this->account_number) return '—';
        $len = strlen($this->account_number);
        if ($len <= 4) return str_repeat('*', $len);
        return str_repeat('*', $len - 4) . substr($this->account_number, -4);
    }
}
