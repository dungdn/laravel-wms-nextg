<?php

namespace App\Repositories;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository implements BaseRepositoryInterface
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    protected function query()
    {
        return $this->model->newQuery();
    }

    public function getAll()
    {
        return $this->query()
            ->latest()
            ->get();
    }

    public function paginate(
        int $perPage = 15
    ) {
        return $this->query()
            ->latest()
            ->paginate($perPage);
    }

    public function find(int $id)
    {
        return $this->query()->find($id);
    }

    public function findOrFail(int $id)
    {
        return $this->query()->findOrFail($id);
    }

    public function create(array $data)
    {
        return $this->query()->create($data);
    }

    public function update(
        int $id,
        array $data
    ) {
        $entity = $this->findOrFail($id);

        $entity->update($data);

        return $entity;
    }

    public function delete(int $id): bool
    {
        return $this->findOrFail($id)->delete();
    }
}
