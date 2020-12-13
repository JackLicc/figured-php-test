<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\InventoryService;

class InventoryImporter extends Command
{
    protected $signature = 'inventory:import {filepath}';

    protected $description = 'import inventory movements';

    /** @var InventoryService */
    private $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        parent::__construct();
        $this->inventoryService = $inventoryService;
    }

    public function handle()
    {
        $filepath = $this->argument('filepath');

        $this->inventoryService->importCSV($filepath);
    }
}
