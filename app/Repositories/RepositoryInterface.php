<?php

namespace App\Repositories;

interface RepositoryInterface
{

    public function all();

    public function findById($id);

    public function findByIdOrFail($id);

    public function find($fieldName, $where);

    public function create($data);

    public function update($id, $data);

    public function delete($id);
}