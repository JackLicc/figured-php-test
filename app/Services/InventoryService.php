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
     * @param  string  $filepath
     * @throws InvalidDataFormatException
     */
    public function importCSV(string $filepath): void
    {
        // 1. check existence
        $dataset = [];
        if (($handle = fopen($filepath, 'rb')) === false) {
            throw new InvalidDataFormatException('Unable to read file');
        }

        // 2. load data to $dataset
        // skip the header
        if (fgetcsv($handle) === false) {
            return;
        }
        // read rows
        while ($data = fgetcsv($handle)) {
            [$date, $type, $quantity, $unitPrice] = $data;
            $date = \DateTime::createFromFormat('d/m/Y', $date)->format('Y-m-d');
            $dataset[] = compact('date', 'type', 'quantity', 'unitPrice');
        }
        fclose($handle);

        // ascending order
        $dataset = collect($dataset)->sortBy('date')->toArray();

        // 3. apply operations
        try {
            DB::beginTransaction();
            foreach ($dataset as $row) {
                switch ($row['type']) {
                    case Inventory::OPERATION_PURCHASE:
                        $this->applyPurchaseOperation($row);
                        break;
                    case Inventory::OPERATION_APPLICATION:
                        $this->applyApplicationOperation($row);
                        break;
                    default:
                        throw new \Exception('unknown operation type: '.$row['type']);
                        break;
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            echo $e->getMessage();
            echo $e->getLine();
            DB::rollBack();
        }
    }

    public function applyPurchaseOperation($operation)
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

    public function applyApplicationOperation(array $operation)
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
                //  row  operation        row  operation
                //   10  11         ===>  0    1
                if ($applyQty >= $row->quantity) {
                    $applyQty -= $row->quantity;
                    $row->quantity = 0;
                    $row->save();
                } else {
                    //  row  operation        row  operation
                    //  10   8         ===>   2     0
                    $row->quantity -= $applyQty;
                    $row->save();
                    break;
                }
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
