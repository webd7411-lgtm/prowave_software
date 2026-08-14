<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class IncomeRecord extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'income_number', 'category_id', 'client_id', 'project_id', 'invoice_id', 'payment_id',
        'amount', 'income_date', 'account_type', 'cash_account_id', 'bank_account_id',
        'payment_method_id', 'reference_number', 'description', 'attachment_path', 'notes',
        'added_by', 'updated_by',
    ];

    protected $casts = ['income_date' => 'date', 'amount' => 'decimal:2'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(IncomeCategory::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
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
        return DB::transaction(function () {
            $record = static::withTrashed()->lockForUpdate()->latest('id')->first();
            $last = $record?->income_number;
            $num = $last ? ((int) substr($last, 4)) + 1 : 1;

            return 'INC-' . str_pad($num, 5, '0', STR_PAD_LEFT);
        });
    }
}
