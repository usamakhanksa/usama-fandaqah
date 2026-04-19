<?php

namespace App\Imports;

use App\Country;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;

class CompaniesImport implements ToModel
{
    /**
     * @param array $row
     *
     * 
     */
    public function model(array $row)
    {
        return $row;
    }
}
