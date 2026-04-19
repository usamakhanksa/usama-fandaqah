<?php

namespace App\Imports;

use App\Country;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;

class CountriesImport implements ToModel
{
    /**
     * @param array $row
     *
     * @return Country|null
     */
    public function model(array $row)
    {
        return $row;
    }
}