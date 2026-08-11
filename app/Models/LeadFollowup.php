<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class LeadFollowup extends Model { protected $fillable=['lead_id','client_id','follow_up_date','follow_up_time','type','assigned_to','notes','status','reminder','reminded_at','created_by']; protected $casts=['follow_up_date'=>'date','reminder'=>'boolean']; public function lead(){return $this->belongsTo(Lead::class);} public function client(){return $this->belongsTo(Client::class);} public function assignee(){return $this->belongsTo(User::class,'assigned_to');} }
