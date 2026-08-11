<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vendor extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'vendor_number', 'name', 'contact_person', 'phone', 'whatsapp', 'email',
        'address', 'category', 'notes', 'status', 'created_by', 'updated_by',
    ];

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function vendorPayments(): HasMany
    {
        return $this->hasMany(VendorPayment::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function totalPaid(): float
    {
        return (float) $this->vendorPayments()->sum('amount');
    }

    public function totalExpenses(): float
    {
        return (float) $this->expenses()->sum('amount');
    }
}
