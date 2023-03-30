<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = ["name", "address", "other_address", "email", "phone", "postal_code", "city"];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
