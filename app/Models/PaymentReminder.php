<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentReminder extends Model
{
    protected $fillable = ['invoice_id', 'client_id', 'channel', 'message', 'sent_at', 'status', 'sent_by'];

    protected $casts = ['sent_at' => 'datetime'];

    public function invoice(): BelongsTo { return $this->belongsTo(Invoice::class); }
    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
    public function sentBy(): BelongsTo { return $this->belongsTo(User::class, 'sent_by'); }
}
