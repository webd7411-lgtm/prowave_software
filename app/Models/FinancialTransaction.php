<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FinancialTransaction extends Model
{
    protected $fillable = [
        'reference', 'type', 'account_type', 'cash_account_id', 'bank_account_id',
        'description', 'debit', 'credit', 'balance_after', 'transaction_date',
        'source_type', 'source_id', 'created_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    public function cashAccount(): BelongsTo
    {
        return $this->belongsTo(CashAccount::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function typeBadge(): string
    {
        return match($this->type) {
            'income'     => 'success',
            'expense'    => 'danger',
            'transfer'   => 'info',
            'payment'    => 'primary',
            'refund'     => 'warning',
            'adjustment' => 'secondary',
            default      => 'secondary',
        };
    }
}
