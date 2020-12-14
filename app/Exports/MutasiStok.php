<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\Barang;
use DB;

class MutasiStok implements FromArray, WithHeadings, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    use Exportable;
    public function __construct($id_barang, $id_supplier, $from, $to)
    {
      $this->id_barang = $id_barang;
      $this->id_supplier = $id_supplier;
      $this->from = $from;
      $this->to = $to;
    }

    public function headings() : array
    {
      return ["Nama Barang","Supplier","Waktu","Masuk","Keluar","Loss"];
    }

    public function array() : array
    {
      $data = Barang::where("id_barang", $this->id_barang)
      ->with(["log_stok_barang" => function($query){
        $query->select("id_barang","id_supplier",
        DB::raw("date(waktu) as waktu"),
        DB::raw("sum(if(status = 'in', jumlah, 0)) as masuk"),
        DB::raw("sum(if(status = 'out', jumlah, 0)) as keluar"),
        DB::raw("if(status = 'out', stok + jumlah, stok - jumlah) as stok"),
        DB::raw("sum(loss) as loss")
        )
        ->where("id_supplier", $this->id_supplier)
        ->whereBetween("waktu",[$this->from, $this->to])
        ->groupBy(DB::raw("date(waktu)"))
        ->orderBy("waktu","asc");
      },"log_stok_barang.supplier"])->first();

      $mutasi = [];
      foreach ($data->log_stok_barang as $sb) {
        $item = [
          "nama_barang" => $data->nama_barang,
          "nama_supplier" => $sb->supplier->nama_supplier,
          "tanggal" => $sb->waktu,
          "masuk" => $sb->masuk,
          "keluar" => $sb->keluar,
          "loss" => $sb->loss
        ];
        array_push($mutasi, $item);
      }
      return $mutasi;
    }
}
