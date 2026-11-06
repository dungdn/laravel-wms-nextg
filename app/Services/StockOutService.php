<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\StockOut;
use App\Repositories\Contracts\StockOutRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockOutService extends BaseService
{
    public function __construct(
        StockOutRepositoryInterface $repository
    ) {
        $this->repository = $repository;
    }

    public function paginateWithFilters(
        ?int $warehouseId = null,
        ?string $search = null,
        int $perPage = 15
    ) {
        return $this->repository->paginateWithFilters($warehouseId, $search, $perPage);
    }

    public function findWithDetails(int $id)
    {
        return $this->repository->findWithDetails($id);
    }

    public function generateReferenceNo(): string
    {
        return $this->repository->generateReferenceNo();
    }

    public function createStockOut(array $data): StockOut
    {
        return DB::transaction(function () use ($data) {
            $referenceNo = ! empty($data['reference_no'])
                ? $data['reference_no']
                : $this->generateReferenceNo();

            // 1. Calculate requested total per product and validate stock availability with lockForUpdate
            $requestedByProduct = [];
            foreach ($data['items'] as $item) {
                $pId = $item['product_id'];
                $requestedByProduct[$pId] = ($requestedByProduct[$pId] ?? 0) + (int) $item['quantity'];
            }

            $inventories = [];
            foreach ($requestedByProduct as $productId => $totalRequested) {
                $inventory = Inventory::with('product')
                    ->where('warehouse_id', $data['warehouse_id'])
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
                        'items' => ["Số lượng xuất cho sản phẩm '{$productName}' vượt quá tồn kho khả dụng (Hiện có: {$available}, yêu cầu: {$totalRequested})."],
                    ]);
                }

                $inventories[$productId] = $inventory;
            }

            // 2. Create StockOut voucher
            $stockOut = $this->repository->create([
                'reference_no' => $referenceNo,
                'warehouse_id' => $data['warehouse_id'],
                'transaction_date' => $data['transaction_date'] ?? now()->toDateString(),
                'status' => $data['status'] ?? 'completed',
                'note' => $data['note'] ?? null,
                'created_by' => auth()->id() ?? $data['created_by'] ?? null,
            ]);

            // 3. Create items, deduct inventory, and record inventory audit log
            foreach ($data['items'] as $item) {
                $stockOut->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => (int) $item['quantity'],
                    'unit_price' => $item['unit_price'] ?? 0,
                ]);

                $inventory = $inventories[$item['product_id']];
                $qtyBefore = (int) $inventory->quantity;
                $qtyChange = (int) $item['quantity'];
                $qtyAfter = $qtyBefore - $qtyChange;

                $inventory->update([
                    'quantity' => $qtyAfter,
                ]);
                $inventory->quantity = $qtyAfter;

                InventoryTransaction::create([
                    'warehouse_id' => $data['warehouse_id'],
                    'product_id' => $item['product_id'],
                    'transaction_type' => 'OUT',
                    'reference_type' => 'stock_out',
                    'reference_id' => $stockOut->id,
                    'qty_before' => $qtyBefore,
                    'qty_change' => -$qtyChange,
                    'qty_after' => $qtyAfter,
                    'created_by' => auth()->id() ?? $data['created_by'] ?? null,
                ]);
            }

            return $stockOut->load(['warehouse', 'items.product', 'creator']);
        });
    }
}
