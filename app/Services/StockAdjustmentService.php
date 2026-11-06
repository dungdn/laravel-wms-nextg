<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\StockAdjustment;
use App\Repositories\Contracts\StockAdjustmentRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockAdjustmentService extends BaseService
{
    public function __construct(
        StockAdjustmentRepositoryInterface $repository
    ) {
        $this->repository = $repository;
    }

    public function paginateWithFilters(
        ?int $warehouseId = null,
        ?string $reason = null,
        ?string $search = null,
        int $perPage = 15
    ) {
        return $this->repository->paginateWithFilters(
            $warehouseId,
            $reason,
            $search,
            $perPage
        );
    }

    public function generateReferenceNo(): string
    {
        return $this->repository->generateReferenceNo();
    }

    public function findWithDetails(int $id)
    {
        return $this->repository->findWithDetails($id);
    }

    public function createStockAdjustment(array $data): StockAdjustment
    {
        return DB::transaction(function () use ($data) {
            $warehouseId = (int) $data['warehouse_id'];

            // 1. Validate that all adjusted quantities are non-negative
            foreach ($data['items'] as $item) {
                if ((int) $item['adjusted_quantity'] < 0) {
                    throw ValidationException::withMessages([
                        'items' => ['Số lượng sau kiểm kê / điều chỉnh không được nhỏ hơn 0.'],
                    ]);
                }
            }

            $referenceNo = ! empty($data['reference_no'])
                ? $data['reference_no']
                : $this->generateReferenceNo();

            // 2. Create Stock Adjustment Header
            $stockAdjustment = $this->repository->create([
                'reference_no' => $referenceNo,
                'warehouse_id' => $warehouseId,
                'adjustment_date' => $data['adjustment_date'] ?? now()->toDateString(),
                'reason' => $data['reason'] ?? 'Cycle Count',
                'status' => $data['status'] ?? 'completed',
                'note' => $data['note'] ?? null,
                'created_by' => auth()->id() ?? $data['created_by'] ?? null,
            ]);

            // 3. Process each item: create item record, reconcile inventory with lock, log audit
            foreach ($data['items'] as $item) {
                $productId = (int) $item['product_id'];
                $adjustedQty = (int) $item['adjusted_quantity'];

                $inventory = Inventory::firstOrCreate(
                    [
                        'warehouse_id' => $warehouseId,
                        'product_id' => $productId,
                    ],
                    [
                        'quantity' => 0,
                    ]
                );

                $inventory = Inventory::where('id', $inventory->id)
                    ->lockForUpdate()
                    ->first();

                $qtyBefore = (int) $inventory->quantity;
                $qtyAfter = $adjustedQty;
                $qtyChange = $qtyAfter - $qtyBefore;

                // Update inventory
                $inventory->update([
                    'quantity' => $qtyAfter,
                ]);

                // Create item
                $stockAdjustment->items()->create([
                    'product_id' => $productId,
                    'current_quantity' => $qtyBefore,
                    'adjusted_quantity' => $qtyAfter,
                    'quantity_change' => $qtyChange,
                    'note' => $item['note'] ?? null,
                ]);

                // Log audit transaction
                InventoryTransaction::create([
                    'warehouse_id' => $warehouseId,
                    'product_id' => $productId,
                    'transaction_type' => 'ADJUSTMENT',
                    'reference_type' => 'stock_adjustment',
                    'reference_id' => $stockAdjustment->id,
                    'qty_before' => $qtyBefore,
                    'qty_change' => $qtyChange,
                    'qty_after' => $qtyAfter,
                    'created_by' => auth()->id() ?? $data['created_by'] ?? null,
                ]);
            }

            return $stockAdjustment->load(['warehouse', 'items.product', 'creator']);
        });
    }
}
