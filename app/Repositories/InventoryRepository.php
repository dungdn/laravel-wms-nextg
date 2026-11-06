<?php

namespace App\Repositories;

use App\Models\Inventory;
use App\Repositories\Contracts\InventoryRepositoryInterface;

class InventoryRepository extends BaseRepository implements InventoryRepositoryInterface
{
    public function __construct(Inventory $model)
    {
        parent::__construct($model);
    }

    public function paginateWithFilters(
        ?int $warehouseId = null,
        ?string $search = null,
        int $perPage = 15
    ) {
        return $this->query()
            ->with(['warehouse', 'product'])
            ->when($warehouseId, function ($query) use ($warehouseId) {
                $query->where('warehouse_id', $warehouseId);
            })
            ->when($search, function ($query) use ($search) {
                $query->whereHas('product', function ($query) use ($search) {
                    $query->where('sku', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate($perPage);
    }

    public function findByWarehouseAndProduct(int $warehouseId, int $productId)
    {
        return $this->query()
            ->where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->first();
    }

    public function getValuationMetrics(?int $warehouseId = null): array
    {
        $query = $this->query()->with('product');

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        $inventories = $query->get();

        $totalUnits = (int) $inventories->sum('quantity');
        $totalValuation = (float) $inventories->sum(function ($inv) {
            return (float) ($inv->quantity * ($inv->product?->cost_price ?? 0));
        });

        $lowStockCount = $inventories->filter(fn ($inv) => $inv->quantity > 0 && $inv->quantity <= 10)->count();
        $outOfStockCount = $inventories->filter(fn ($inv) => $inv->quantity <= 0)->count();

        return [
            'total_units' => $totalUnits,
            'total_valuation' => $totalValuation,
            'low_stock_count' => $lowStockCount,
            'out_of_stock_count' => $outOfStockCount,
        ];
    }

    public function getWarehouseStockSummary(): array
    {
        $inventories = $this->query()
            ->with(['warehouse', 'product'])
            ->get();

        $grouped = $inventories->groupBy('warehouse_id');
        $summary = [];

        foreach ($grouped as $whId => $items) {
            $warehouse = $items->first()?->warehouse;
            if (! $warehouse) {
                continue;
            }

            $summary[] = [
                'warehouse_id' => $warehouse->id,
                'warehouse_name' => $warehouse->name,
                'warehouse_code' => $warehouse->code,
                'total_products' => $items->count(),
                'total_units' => (int) $items->sum('quantity'),
                'total_valuation' => (float) $items->sum(function ($inv) {
                    return (float) ($inv->quantity * ($inv->product?->cost_price ?? 0));
                }),
                'low_stock_items' => $items->filter(fn ($inv) => $inv->quantity > 0 && $inv->quantity <= 10)->count(),
                'out_of_stock_items' => $items->filter(fn ($inv) => $inv->quantity <= 0)->count(),
            ];
        }

        return $summary;
    }

    public function updateOrCreate(array $attributes, array $values)
    {
        return $this->query()->updateOrCreate($attributes, $values);
    }
}
