<?php declare(strict_types=1);

namespace App\Services;

use App\Exceptions\InsufficientInventoryException;
use App\Exceptions\InvalidDataFormatException;
use App\Models\Inventory;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Routing\Exception\InvalidParameterException;

class InventoryService
{
    /**
     * CSV example:
     *  date,         type,         quantity, unitPrice
     *  31/10/2019,    Purchase,    10 ,      20
     *  01/11/2019,    Application, -5 ,      (N/A)
     *
     *
     * @param  string  $filePath
     * @throws InvalidDataFormatException
     */
    public function importCSV(string $filePath): void
    {
        // 1. load data to $dataset
        $operations = $this->loadOperationsFromCSV($filePath);

        // 2. apply operations
        foreach ($operations as $operation) {
            $this->processOperation($operation);
        }
    }

    public function loadOperationsFromCSV($filePath): array
    {
        // 1. check existence
        if (($handler = fopen($filePath, 'rb')) === false) {
            throw new InvalidDataFormatException('Unable to read file');
        }
        // skip the header
        if (fgetcsv($handler) === false) {
            fclose($handler);
            return [];
        }
        // read rows
        $operations = [];
        while ($data = fgetcsv($handler)) {
            [$date, $type, $quantity, $unitPrice] = $data;
            $date = \DateTime::createFromFormat('d/m/Y', $date)->format('Y-m-d');
            $operations[] = compact('date', 'type', 'quantity', 'unitPrice');
        }

        fclose($handler);

        // ascending order
        usort($operations, static function($op1, $op2) {
            return $op1['date'] <=> $op2['date'];
        });

        return $operations;
    }

    public function processOperation($operation): void
    {
        switch ($operation['type']) {
            case Inventory::OPERATION_PURCHASE:
                $this->applyPurchaseOperation($operation);
                break;
            case Inventory::OPERATION_APPLICATION:
                $this->applyApplicationOperation($operation);
                break;
            default:
                throw new \Exception('unknown operation type: '.$operation['type']);
                break;
        }
    }

    public function applyPurchaseOperation($operation): void
    {
        if (!isset($operation['date']) || !isset($operation['quantity']) || !isset($operation['unitPrice'])) {
            throw new InvalidParameterException('Missing parameters for purchase operation');
        }

        Inventory::create([
            'operation_date' => $operation['date'],
            'quantity' => $operation['quantity'],
            'unit_price' => $operation['unitPrice']
        ]);
    }

    public function applyApplicationOperation(array $operation): void
    {
        // 1. input validation
        if (!isset($operation['quantity']) || $operation['quantity'] >= 0) {
            throw new InvalidParameterException('Invalid parameter');
        }

        // 2. current total quantity should > $operation['quantity']
        $applyQty = abs($operation['quantity']);
        $sum = Inventory::available()->sum('quantity');
        if ($sum < $applyQty) {
            throw new InsufficientInventoryException('Insufficient inventory');
        }

        // 3. apply consumption on each available(quantity > 0) row
        try {
            DB::beginTransaction();
            $rows = Inventory::available()->orderBy('id')->get();
            foreach ($rows as $row) {
                $applyQty = $row->applyApplicationOperation($applyQty);
                if ($applyQty === 0) {
                    break;
                }
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getTotalAvailableQuantity()
    {
        return Inventory::available()->sum('quantity');
    }

    public function getValuationOfApplication($applyQty)
    {
        // 1. check total available quantity
        $sum = Inventory::available()->sum('quantity');
        if ($sum < $applyQty) {
            throw new InsufficientInventoryException('Insufficient inventory');
        }

        // 2. apply application and calculate applied valuation
        $valuation = 0;
        $rows = Inventory::available()->orderBy('id')->get();
        foreach ($rows as $row) {
            //   applyQty | quantity | unit_price       applyQty | valuation
            //   100      | 40       | 7          ====> 40       | 280
            if ($applyQty >= $row->quantity) {
                $valuation += $row->quantity * $row->unit_price;
                $applyQty -= $row->quantity;
            } else {
                //   applyQty | quantity | unit_price         applyQty | valuation
                //   10       | 100      | 4.5        ====>   10       | 45
                $valuation += $applyQty * $row->unit_price;
                break;
            }
        }

        return $valuation;
    }
}
