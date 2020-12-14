<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\Orders;
use DB;

class Order implements FromArray, WithHeadings, ShouldAutoSize
{
    use Exportable;
    public function __construct($from, $to, $find)
    {
      $this->find = $find == null ? "" : $find;
      $this->from = $from." 00:00:00";
      $this->to = $to." 23:59:59";
    }

    public function headings() : array
    {
      return [
        "ID Order","Pembeli","Kasir","Sopir","Waktu Order","Waktu Kirim",
        "Barang","Qty","Harga Satuan","Total"
      ];
    }

    public function array() : array
    {
      $orders = Orders::whereBetween("waktu_order", [$this->from, $this->to])->with([
        "pembeli","users", "sopir","status_orders",
        "detail_orders","detail_orders.barang","detail_orders.pack"
      ])->where(function($q){
        $q->whereHas("detail_orders.barang", function($query){
          $query->where("nama_barang","like","%$this->find%");
        })
        ->orWhereHas("detail_orders.pack", function($query){
          $query->where("nama_pack","like","%$this->find%");
        })
        ->orWhereHas("pembeli", function($query){
          $query->where("nama","like","%$this->find%");
        })
        ->orWhereHas("sopir", function($query){
          $query->where("nama","like","%$this->find%");
        })
        ->orWhereHas("users", function($query){
          $query->where("nama","like","%$this->find%");
        })
        ->orWhere("po","like","%$this->find%")
        ->orWhere("invoice","like","%$this->find%");
      })->orderBy("waktu_order","desc");

      $data = [];
      foreach ($orders->get() as $o) {
        foreach ($o->detail_orders as $do) {
          $item = [
            "id_orders" => $o->id_orders,
            "pembeli" => $o->pembeli->nama,
            "kasir" => $o->users->nama,
            "sopir" => $o->sopir->nama,
            "waktu_order" => $o->waktu_order,
            "waktu_kirim" => $o->waktu_pengiriman,
            "barang" => $do->barang->nama_barang,
            "qty" => $do->jumlah_barang,
            "harga_satuan" => $do->harga_beli,
            "total" => $do->jumlah_barang * $do->harga_beli
          ];
          array_push($data, $item);
        }
      }

      return $data;
    }
}
