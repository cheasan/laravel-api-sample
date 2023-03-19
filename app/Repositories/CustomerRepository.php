<?php

namespace App\Repositories;

use App\Models\Customer;

class CustomerRepository extends Repository
{

    public function __construct(Customer $customer)
    {
        parent::__construct($customer);
    }

}