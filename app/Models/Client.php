<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_number', 'company_name', 'contact_person', 'phone', 'email',
        'website', 'address', 'city', 'country', 'industry', 'client_type',
        'account_manager_id', 'status', 'notes', 'created_by', 'updated_by',
    ];

    public function manager()
    {
        return $this->belongsTo(User::class, 'account_manager_id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function ledger()
    {
        return $this->hasMany(ClientLedger::class)
            ->orderBy('transaction_date', 'asc')
            ->orderByRaw("CASE WHEN type = 'project_budget' THEN 1 WHEN debit > 0 THEN 2 ELSE 3 END")
            ->orderBy('id', 'asc');
    }

    public function quotations()
    {
        return $this->hasMany(Quotation::class);
    }

    public function outstanding(): float
    {
        if ($this->ledger()->exists()) {
            return (float) $this->ledger()->sum('debit') - (float) $this->ledger()->sum('credit');
        }

        return (float) $this->invoices()->sum('total') - (float) $this->payments()->sum('amount');
    }

    public static function generateNumber(): string
    {
        return DB::transaction(function () {
            $record = static::withTrashed()->lockForUpdate()->latest('id')->first();
            $last = $record?->client_number;

            $num = 1;
            if ($last && preg_match('/(\d+)$/', $last, $matches)) {
                $num = ((int) $matches[1]) + 1;
            } elseif ($record) {
                $num = $record->id + 1;
            }

            return 'CL-' . str_pad((string) $num, 4, '0', STR_PAD_LEFT);
        });
    }
}
