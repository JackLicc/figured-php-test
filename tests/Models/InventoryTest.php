<?php

namespace Tests\Models;

use App\Models\Inventory;
use PHPUnit\Framework\TestCase;

class InventoryTest extends TestCase
{
    public function testApplyApplicationOperation_Enough()
    {
        $inventory = $this->createPartialMock(Inventory::class, ['save']);
        $inventory->expects(self::once())
            ->method('save');
        $inventory->quantity = 10;

        self::assertEquals(0, $inventory->applyApplicationOperation(6));
        self::assertEquals(4, $inventory->quantity);
    }

    public function testApplyApplicationOperation_NotEnough()
    {
        $inventory = $this->createPartialMock(Inventory::class, ['save']);
        $inventory->expects(self::once())
            ->method('save');
        $inventory->quantity = 10;

        self::assertEquals(2, $inventory->applyApplicationOperation(12));
        self::assertEquals(0, $inventory->quantity);
    }
}
