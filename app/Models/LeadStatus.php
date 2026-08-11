<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadStatus extends Model
{
    protected $fillable = ['name', 'slug', 'color', 'sort_order', 'is_active'];
}