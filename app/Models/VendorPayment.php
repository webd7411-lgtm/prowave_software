<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class VendorPayment extends Model
{
    protected $fillable = [
        'payment_number', 'vendor_id', 'expense_id', 'amount', 'payment_date',
        'account_type', 'cash_account_id', 'bank_account_id', 'payment_method_id',
        'reference_number', 'notes', 'created_by',
    ];

    protected $casts = ['payment_date' => 'date', 'amount' => 'decimal:2'];

    public function vendor(): BelongsTo { return $this->belongsTo(Vendor::class); }
    public function expense(): BelongsTo { return $this->belongsTo(Expense::class); }
    public function cashAccount(): BelongsTo { return $this->belongsTo(CashAccount::class); }
    public function bankAccount(): BelongsTo { return $this->belongsTo(BankAccount::class); }
    public function paymentMethod(): BelongsTo { return $this->belongsTo(PaymentMethod::class); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    public static function generateNumber(): string
    {
        return DB::transaction(function () {
            $record = static::lockForUpdate()->latest('id')->first();
            $last = $record?->payment_number;
            $num = $last ? ((int) substr($last, 4)) + 1 : 1;

            return 'VPY-' . str_pad($num, 5, '0', STR_PAD_LEFT);
        });
    }
}
