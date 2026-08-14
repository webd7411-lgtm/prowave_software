<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Lead extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'lead_number', 'company_name', 'contact_person', 'phone', 'email',
        'website', 'address', 'city', 'country', 'source', 'service_interested',
        'estimated_budget', 'owner_id', 'status', 'stage', 'priority',
        'expected_closing_date', 'notes', 'converted_client_id', 'converted_at',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'estimated_budget' => 'decimal:2',
        'expected_closing_date' => 'date',
        'converted_at' => 'datetime',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'converted_client_id');
    }

    public function followups()
    {
        return $this->hasMany(LeadFollowup::class);
    }

    public function activities()
    {
        return $this->hasMany(LeadActivity::class);
    }

    public function stageHistories()
    {
        return $this->hasMany(LeadStageHistory::class);
    }

    public static function generateNumber(): string
    {
        return DB::transaction(function () {
            $record = static::withTrashed()->lockForUpdate()->latest('id')->first();
            $last = $record?->lead_number;
            
            $num = 1;
            if ($last && preg_match('/(\d+)$/', $last, $matches)) {
                $num = ((int) $matches[1]) + 1;
            }

            return str_pad((string) $num, 4, '0', STR_PAD_LEFT);
        });
    }
}
