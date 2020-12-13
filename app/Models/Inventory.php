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

    public function applyApplicationOperation(int $applyQty): int {
        //  row  operation        row  operation
        //   10  11         ===>  0    1
        if ($applyQty >= $this->quantity) {
            $applyQty -= $this->quantity;
            $this->quantity = 0;
        } else {
            //  row  operation        row  operation
            //  10   8         ===>   2     0
            $this->quantity -= $applyQty;
            $applyQty = 0;
        }
        $this->save();
        return $applyQty;
    }
}
