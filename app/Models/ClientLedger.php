<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientLedger extends Model
{
    protected $fillable = [
        'client_id',
        'transaction_date',
        'reference',
        'type',
        'description',
        'debit',
        'credit',
        'balance',
        'source_type',
        'source_id',
        'created_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function source()
    {
        return $this->morphTo();
    }

    public static function recalculate($clientId): void
    {
        $entries = static::where('client_id', $clientId)
            ->orderBy('transaction_date', 'asc')
            ->orderByRaw("CASE WHEN type = 'project_budget' THEN 1 WHEN debit > 0 THEN 2 ELSE 3 END")
            ->orderBy('id', 'asc')
            ->get();

        $running = 0;
        foreach ($entries as $entry) {
            $running += ((float) $entry->debit - (float) $entry->credit);
            if ((float) $entry->balance !== (float) $running) {
                $entry->update(['balance' => $running]);
            }
        }
    }

    public static function syncProjectBudgets($clientId = null): void
    {
        // Delete all project_budget ledger entries to prevent double counting with invoices
        $deleteQuery = static::where('type', 'project_budget');
        if ($clientId) {
            $deleteQuery->where('client_id', $clientId);
        }
        $deleteQuery->delete();

        // Auto-link any payments created for a client without project_id to their project
        $unassignedPaymentsQuery = Payment::whereNull('project_id')->whereNotNull('client_id');
        if ($clientId) {
            $unassignedPaymentsQuery->where('client_id', $clientId);
        }
        foreach ($unassignedPaymentsQuery->get() as $payment) {
            $firstProject = Project::where('client_id', $payment->client_id)->orderBy('id', 'asc')->first();
            if ($firstProject) {
                $payment->update(['project_id' => $firstProject->id]);
            }
        }

        $clientIds = $clientId ? [$clientId] : static::pluck('client_id')->unique();
        foreach ($clientIds as $cId) {
            if ($cId) {
                static::recalculate($cId);
            }
        }
    }
}

