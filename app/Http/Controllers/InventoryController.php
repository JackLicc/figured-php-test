<?php

namespace App\Http\Controllers;

use App\Services\InventoryService;

class InventoryController extends Controller
{
    /** @var InventoryService */
    private $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function index()
    {
        $availableQty = $this->inventoryService->getTotalAvailableQuantity();
        return view('inventory.index', ['availableQuantity' => $availableQty]);
    }

    public function query($quantity)
    {
        $valuation = $this->inventoryService->getValuationOfApplication($quantity);
        return response()->json(['valuation' => number_format($valuation, 2, '.', '')]);
    }
}
