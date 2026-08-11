<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'expense_number', 'category_id', 'amount', 'expense_date', 'account_type',
        'cash_account_id', 'bank_account_id', 'payment_method_id', 'reference_number',
        'description', 'vendor_id', 'project_id', 'receipt_path', 'status',
        'approved_by', 'approved_at', 'approval_remarks', 'notes', 'added_by', 'updated_by', 'deleted_by',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'approved_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function cashAccount(): BelongsTo
    {
        return $this->belongsTo(CashAccount::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function statusBadge(): string
    {
        return match($this->status) {
            'approved' => 'success',
            'rejected' => 'danger',
            'paid'     => 'primary',
            default    => 'warning',
        };
    }

    public function accountLabel(): string
    {
        if ($this->account_type === 'cash' && $this->cashAccount) {
            return $this->cashAccount->name;
        }
        if ($this->account_type === 'bank' && $this->bankAccount) {
            return $this->bankAccount->bank_name . ' - ' . $this->bankAccount->account_title;
        }
        return ucfirst($this->account_type);
    }

    public static function generateNumber(): string
    {
        $last = static::withTrashed()->latest('id')->value('expense_number');
        $num = $last ? ((int) substr($last, 4)) + 1 : 1;
        return 'EXP-' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }
}
