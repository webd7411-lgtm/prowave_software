<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class Quotation extends Model { protected $fillable=['quotation_number','client_id','lead_id','quotation_date','valid_until','subtotal','discount','tax','total','notes','terms','status','created_by']; protected $casts=['quotation_date'=>'date','valid_until'=>'date']; public function client(){return $this->belongsTo(Client::class);} public function items(){return $this->hasMany(QuotationItem::class);} }
