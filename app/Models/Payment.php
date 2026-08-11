<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class Payment extends Model { protected $fillable=['payment_number','client_id','invoice_id','payment_date','amount','method','reference_number','notes','created_by']; protected $casts=['payment_date'=>'date','amount'=>'decimal:2']; public function client(){return $this->belongsTo(Client::class);} public function invoice(){return $this->belongsTo(Invoice::class);} }
