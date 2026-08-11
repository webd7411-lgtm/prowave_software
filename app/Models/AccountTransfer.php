<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountTransfer extends Model
{
    protected $fillable = [
        'transfer_number', 'from_account_type', 'from_cash_account_id', 'from_bank_account_id',
        'to_account_type', 'to_cash_account_id', 'to_bank_account_id',
        'amount', 'transfer_date', 'reference', 'description', 'created_by',
    ];

    protected $casts = ['transfer_date' => 'date', 'amount' => 'decimal:2'];

    public function fromCashAccount(): BelongsTo { return $this->belongsTo(CashAccount::class, 'from_cash_account_id'); }
    public function fromBankAccount(): BelongsTo { return $this->belongsTo(BankAccount::class, 'from_bank_account_id'); }
    public function toCashAccount(): BelongsTo { return $this->belongsTo(CashAccount::class, 'to_cash_account_id'); }
    public function toBankAccount(): BelongsTo { return $this->belongsTo(BankAccount::class, 'to_bank_account_id'); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    public function fromLabel(): string
    {
        if ($this->from_account_type === 'cash' && $this->fromCashAccount) return $this->fromCashAccount->name;
        if ($this->from_account_type === 'bank' && $this->fromBankAccount) return $this->fromBankAccount->bank_name;
        return ucfirst($this->from_account_type);
    }

    public function toLabel(): string
    {
        if ($this->to_account_type === 'cash' && $this->toCashAccount) return $this->toCashAccount->name;
        if ($this->to_account_type === 'bank' && $this->toBankAccount) return $this->toBankAccount->bank_name;
        return ucfirst($this->to_account_type);
    }

    public static function generateNumber(): string
    {
        $last = static::latest('id')->value('transfer_number');
        $num = $last ? ((int) substr($last, 4)) + 1 : 1;
        return 'TRF-' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }
}
