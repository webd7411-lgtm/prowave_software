<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class ClientLedger extends Model { protected $fillable=['client_id','transaction_date','reference','type','description','debit','credit','balance','source_type','source_id','created_by']; protected $casts=['transaction_date'=>'date']; public function client(){return $this->belongsTo(Client::class);} public function source(){return $this->morphTo();} }
