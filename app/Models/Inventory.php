<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $table = 'inventory';

    protected $hidden = ['created_at', 'updated_at'];

    protected $fillable = ['operation_date', 'quantity', 'unit_price'];

    const OPERATION_PURCHASE = 'Purchase';

    const OPERATION_APPLICATION = 'Application';

    public function scopeAvailable($query)
    {
        return $query->where('quantity', '>', 0);
    }
}
