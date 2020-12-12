<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use App\Barang;
use App\Orders;

class TestExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Orders::with("detail_orders")->get();
    }
}
