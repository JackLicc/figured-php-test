<?php

namespace Tests\Services;

use App\Models\Inventory;
use App\Services\InventoryService;
use PHPUnit\Framework\TestCase;

class InventoryServiceTest extends TestCase
{

    public function testApplyOperation_Purchase()
    {
        $inventoryService = $this->createPartialMock(InventoryService::class,
            ['applyPurchaseOperation', 'applyApplicationOperation']);

        $operation = [
            'type' => Inventory::OPERATION_PURCHASE
        ];
        $inventoryService->expects(self::once())
            ->method('applyPurchaseOperation')
            ->with();
        $inventoryService->expects(self::never())
            ->method('applyApplicationOperation');

        $inventoryService->applyOperation($operation);
    }

    public function testApplyOperation_Application()
    {
        $inventoryService = $this->createPartialMock(InventoryService::class,
            ['applyPurchaseOperation', 'applyApplicationOperation']);


        $operation = [
            'type' => Inventory::OPERATION_APPLICATION
        ];
        $inventoryService->expects(self::never())
            ->method('applyPurchaseOperation')
            ->with();
        $inventoryService->expects(self::once())
            ->method('applyApplicationOperation');

        $inventoryService->applyOperation($operation);
    }

    public function testLoadOperationsFromCSV()
    {
        $tempFile = tempnam("/tmp", 'operation');

        $operations = [
            ['Date', 'Type', 'Quantity', 'Unit Price'],
            ['08/05/2020', 'Purchase', 10, 5.5],
            ['07/05/2019', 'Purchase', 8, 4],
            ['10/12/2020', 'Purchase', 15, 3],
            ['09/10/2020', 'Application', -10, '']
        ];

        $fp = fopen($tempFile, 'w');

        foreach ($operations as $operation) {
            fputcsv($fp, $operation);
        }

        fclose($fp);

        $inventoryService = new InventoryService();
        $actualResults = $inventoryService->loadOperationsFromCSV($tempFile);

        self::assertCount(4, $actualResults);
        self::assertEquals(['date' => '2019-05-07', 'type' => 'Purchase', 'quantity' => '8', 'unitPrice' => '4'], $actualResults[0]);
        self::assertEquals(['date' => '2020-05-08', 'type' => 'Purchase', 'quantity' => '10', 'unitPrice' => '5.5'], $actualResults[1]);
        self::assertEquals(['date' => '2020-10-09', 'type' => 'Application', 'quantity' => '-10', 'unitPrice' => ''], $actualResults[2]);
        self::assertEquals(['date' => '2020-12-10', 'type' => 'Purchase', 'quantity' => '15', 'unitPrice' => '3'], $actualResults[3]);

        @unlink($tempFile);
    }
}
