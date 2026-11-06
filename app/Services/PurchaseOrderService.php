<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Repositories\Contracts\PurchaseOrderRepositoryInterface;
use Illuminate\Support\Facades\DB;

class PurchaseOrderService extends BaseService
{
    public function __construct(
        PurchaseOrderRepositoryInterface $repository
    ) {
        $this->repository = $repository;
    }

    public function paginateWithFilters(
        ?int $warehouseId = null,
        ?int $supplierId = null,
        ?string $status = null,
        ?string $search = null,
        int $perPage = 15
    ) {
        return $this->repository->paginateWithFilters(
            $warehouseId,
            $supplierId,
            $status,
            $search,
            $perPage
        );
    }

    public function generateOrderNumber(): string
    {
        return $this->repository->generateOrderNumber();
    }

    public function findWithDetails(int $id)
    {
        return $this->repository->findWithDetails($id);
    }

    public function createPurchaseOrder(array $data): PurchaseOrder
    {
        return DB::transaction(function () use ($data) {
            $poNumber = ! empty($data['po_number'])
                ? $data['po_number']
                : $this->generateOrderNumber();

            $po = $this->repository->create([
                'po_number' => $poNumber,
                'supplier_id' => $data['supplier_id'],
                'warehouse_id' => $data['warehouse_id'],
                'order_date' => $data['order_date'] ?? now()->toDateString(),
                'expected_delivery_date' => $data['expected_delivery_date'] ?? null,
                'status' => $data['status'] ?? 'pending',
                'note' => $data['note'] ?? null,
                'created_by' => auth()->id() ?? $data['created_by'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $po->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => (int) $item['quantity'],
                    'unit_cost' => $item['unit_cost'] ?? 0,
                    'received_quantity' => 0,
                ]);
            }

            return $po->load(['supplier', 'warehouse', 'items.product', 'creator']);
        });
    }

    public function updateStatus(int $id, string $status): PurchaseOrder
    {
        $po = $this->repository->findOrFail($id);
        $po->update(['status' => $status]);

        return $po;
    }
}
