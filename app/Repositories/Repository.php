<?php

namespace App\Repositories;

use App\Repositories\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class Repository implements RepositoryInterface
{

    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Get everything in the table
     *
     * @return $this->model
     */
    public function all()
    {
        return $this->model->all();
    }

    /**
     * Get one row in the table by id
     *
     * @param [int] $id
     * @return $this->model
     */
    public function findById($id)
    {
        return $this->model->find($id);
    }

    public function findByIdOrFail($id)
    {
        return $this->model->findOrFail($id);
    }

    public function find($fieldName, $where)
    {
        return $this->model->where($fieldName, '=', $where)->first();
    }

    public function create($data)
    {
        return $this->model->create($data);
    }

    public function update($data, $id)
    {
        $record = $this->model->find($id);
        return $record->update($data);
    }

    public function delete($id)
    {
        // to implement soft delete? 

        return $this->model->destroy($id);
    }
}
