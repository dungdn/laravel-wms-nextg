<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\StockIn;
use App\Repositories\Contracts\StockInRepositoryInterface;
use Illuminate\Support\Facades\DB;

class StockInService extends BaseService
{
    public function __construct(
        StockInRepositoryInterface $repository
    ) {
        $this->repository = $repository;
    }

    public function paginateWithFilters(
        ?int $warehouseId = null,
        ?int $supplierId = null,
        ?string $search = null,
        int $perPage = 15
    ) {
        return $this->repository->paginateWithFilters($warehouseId, $supplierId, $search, $perPage);
    }

    public function generateReferenceNo(): string
    {
        return $this->repository->generateReferenceNo();
    }

    public function createStockIn(array $data): StockIn
    {
        return DB::transaction(function () use ($data) {
            $referenceNo = ! empty($data['reference_no'])
                ? $data['reference_no']
                : $this->generateReferenceNo();

            $stockIn = $this->repository->create([
                'reference_no' => $referenceNo,
                'supplier_id' => $data['supplier_id'],
                'warehouse_id' => $data['warehouse_id'],
                'transaction_date' => $data['transaction_date'] ?? now()->toDateString(),
                'status' => $data['status'] ?? 'completed',
                'note' => $data['note'] ?? null,
                'created_by' => auth()->id() ?? $data['created_by'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                // 1. Create stock in item
                $stockIn->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => (int) $item['quantity'],
                    'unit_cost' => $item['unit_cost'] ?? 0,
                ]);

                // 2. Fetch or initialize inventory
                $inventory = Inventory::firstOrCreate(
                    [
                        'warehouse_id' => $data['warehouse_id'],
                        'product_id' => $item['product_id'],
                    ],
                    [
                        'quantity' => 0,
                    ]
                );

                $qtyBefore = (int) $inventory->quantity;
                $qtyChange = (int) $item['quantity'];
                $qtyAfter = $qtyBefore + $qtyChange;

                // 3. Update inventory
                $inventory->update([
                    'quantity' => $qtyAfter,
                ]);

                // 4. Create inventory audit transaction
                InventoryTransaction::create([
                    'warehouse_id' => $data['warehouse_id'],
                    'product_id' => $item['product_id'],
                    'transaction_type' => 'IN',
                    'reference_type' => 'stock_in',
                    'reference_id' => $stockIn->id,
                    'qty_before' => $qtyBefore,
                    'qty_change' => $qtyChange,
                    'qty_after' => $qtyAfter,
                    'created_by' => auth()->id() ?? $data['created_by'] ?? null,
                ]);
            }

            return $stockIn->load(['supplier', 'warehouse', 'items.product', 'creator']);
        });
    }
}
