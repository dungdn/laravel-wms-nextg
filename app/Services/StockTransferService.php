<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Repositories\Contracts\StockTransferRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockTransferService extends BaseService
{
    public function __construct(
        StockTransferRepositoryInterface $repository
    ) {
        $this->repository = $repository;
    }

    public function paginateWithFilters(
        ?int $fromWarehouseId = null,
        ?int $toWarehouseId = null,
        ?string $search = null,
        int $perPage = 15
    ) {
        return $this->repository->paginateWithFilters(
            $fromWarehouseId,
            $toWarehouseId,
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

    public function createStockTransfer(array $data): StockTransfer
    {
        return DB::transaction(function () use ($data) {
            $fromWarehouseId = (int) $data['from_warehouse_id'];
            $toWarehouseId = (int) $data['to_warehouse_id'];

            if ($fromWarehouseId === $toWarehouseId) {
                throw ValidationException::withMessages([
                    'to_warehouse_id' => ['Kho xuất và kho nhập không được trùng nhau.'],
                ]);
            }

            $referenceNo = ! empty($data['reference_no'])
                ? $data['reference_no']
                : $this->generateReferenceNo();

            // 1. Group requested quantity per product & validate stock availability at source warehouse
            $requestedByProduct = [];
            foreach ($data['items'] as $item) {
                $pId = $item['product_id'];
                $requestedByProduct[$pId] = ($requestedByProduct[$pId] ?? 0) + (int) $item['quantity'];
            }

            $sourceInventories = [];
            foreach ($requestedByProduct as $productId => $totalRequested) {
                $inventory = Inventory::with('product')
                    ->where('warehouse_id', $fromWarehouseId)
                    ->where('product_id', $productId)
                    ->lockForUpdate()
                    ->first();

                $available = $inventory ? (int) $inventory->quantity : 0;

                if ($available < $totalRequested) {
                    $productName = $inventory?->product?->name;
                    if (! $productName) {
                        $product = Product::find($productId);
                        $productName = $product ? $product->name : "ID #{$productId}";
                    }

                    throw ValidationException::withMessages([
                        'items' => ["Số lượng chuyển cho sản phẩm '{$productName}' vượt quá tồn kho khả dụng tại kho nguồn (Hiện có: {$available}, yêu cầu: {$totalRequested})."],
                    ]);
                }

                $sourceInventories[$productId] = $inventory;
            }

            // 2. Create Stock Transfer record
            $stockTransfer = $this->repository->create([
                'reference_no' => $referenceNo,
                'from_warehouse_id' => $fromWarehouseId,
                'to_warehouse_id' => $toWarehouseId,
                'transfer_date' => $data['transfer_date'] ?? now()->toDateString(),
                'status' => $data['status'] ?? 'completed',
                'note' => $data['note'] ?? null,
                'created_by' => auth()->id() ?? $data['created_by'] ?? null,
            ]);

            // 3. Process items: create item, deduct from source, add to destination, write audit logs
            foreach ($data['items'] as $item) {
                $productId = (int) $item['product_id'];
                $quantity = (int) $item['quantity'];

                // 3.1 Create transfer item
                $stockTransfer->items()->create([
                    'product_id' => $productId,
                    'quantity' => $quantity,
                ]);

                // 3.2 Deduct from source warehouse
                $sourceInv = $sourceInventories[$productId];
                $srcQtyBefore = (int) $sourceInv->quantity;
                $srcQtyAfter = $srcQtyBefore - $quantity;

                $sourceInv->update(['quantity' => $srcQtyAfter]);
                $sourceInv->quantity = $srcQtyAfter;

                InventoryTransaction::create([
                    'warehouse_id' => $fromWarehouseId,
                    'product_id' => $productId,
                    'transaction_type' => 'TRANSFER_OUT',
                    'reference_type' => 'stock_transfer',
                    'reference_id' => $stockTransfer->id,
                    'qty_before' => $srcQtyBefore,
                    'qty_change' => -$quantity,
                    'qty_after' => $srcQtyAfter,
                    'created_by' => auth()->id() ?? $data['created_by'] ?? null,
                ]);

                // 3.3 Add to destination warehouse
                $destInv = Inventory::firstOrCreate(
                    [
                        'warehouse_id' => $toWarehouseId,
                        'product_id' => $productId,
                    ],
                    [
                        'quantity' => 0,
                    ]
                );

                $destQtyBefore = (int) $destInv->quantity;
                $destQtyAfter = $destQtyBefore + $quantity;

                $destInv->update(['quantity' => $destQtyAfter]);

                InventoryTransaction::create([
                    'warehouse_id' => $toWarehouseId,
                    'product_id' => $productId,
                    'transaction_type' => 'TRANSFER_IN',
                    'reference_type' => 'stock_transfer',
                    'reference_id' => $stockTransfer->id,
                    'qty_before' => $destQtyBefore,
                    'qty_change' => $quantity,
                    'qty_after' => $destQtyAfter,
                    'created_by' => auth()->id() ?? $data['created_by'] ?? null,
                ]);
            }

            return $stockTransfer->load(['fromWarehouse', 'toWarehouse', 'items.product', 'creator']);
        });
    }
}
