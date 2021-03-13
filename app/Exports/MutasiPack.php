<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\Pack;
use App\LogPack;
use DB;

class MutasiPack implements FromArray, WithHeadings, ShouldAutoSize
{
    use Exportable;

    public function __construct($id_pack, $from, $to, $id_pembeli = null)
    {
      $this->id_pack = $id_pack;
      $this->from = $from." 00:00:00";
      $this->to = $to." 23:59:59";
      $this->id_pembeli = $id_pembeli;
    }

    public function headings() : array
    {
      if ($this->id_pembeli === null) {
        return ["Nama Pack","Waktu","Masuk","Keluar","Stok","Beli","Harga"];
      } else {
        return ["Nama Pack","Waktu","Masuk","Keluar","Sisa","Beli","Harga"];
      }
    }

    public function array() : array
    {
      $stok = 0;
      if ($this->id_pembeli != null) {
        $mutasi = Pack::where("id_pack",$this->id_pack)->with(["log_pack" => function($query){
          $query->select("id_pack",
            DB::raw("date(waktu) as waktu"),
            DB::raw("sum(if(status = 'in', jumlah, 0)) as masuk"),
            DB::raw("sum(if(status = 'out', jumlah, 0)) as keluar"),
            DB::raw("if(status = 'out', stok + jumlah, stok - jumlah) as stok"),
            DB::raw("sum(if(beli = '1', jumlah, 0)) as beli"),
            DB::raw("sum(if(beli = '1', jumlah * harga, 0)) as harga")
            )
          ->whereBetween("waktu",[$this->from, $this->to])
          ->where("id_pembeli", $this->id_pembeli)
          ->groupBy(DB::raw("date(waktu)"))
          ->orderBy("waktu","asc");
        }])->first();

        $logPack = LogPack::select(
          DB::raw("sum(if(status = 'in', jumlah, 0)) as masuk"),
          DB::raw("sum(if(status = 'out', jumlah, 0)) as keluar"))
        ->where("waktu","<",$this->from)
        ->where("id_pack",$this->id_pack)
        ->where("id_pembeli", $this->id_pembeli)->first();
        $stok = $logPack->keluar - $logPack->masuk;

      } else {
        $mutasi = Pack::where("id_pack",$this->id_pack)->with(["log_pack" => function($query){
          $query->select("id_pack",
            DB::raw("date(waktu) as waktu"),
            DB::raw("sum(if(status = 'in', jumlah, 0)) as masuk"),
            DB::raw("sum(if(status = 'out', jumlah, 0)) as keluar"),
            DB::raw("if(status = 'out', stok + jumlah, stok - jumlah) as stok"),
            DB::raw("sum(if(beli = '1', jumlah, 0)) as beli"),
            DB::raw("sum(if(beli = '1', jumlah * harga, 0)) as harga")
            )
          ->whereBetween("waktu",[$this->from, $this->to])
          ->groupBy(DB::raw("date(waktu)"))
          ->orderBy("waktu","asc");
        }])->first();
      }

      $mutasiPack = [];
      if ($this->id_pembeli != null) {
        foreach ($mutasi->log_pack as $sb) {
          $stok = $stok + $sb->keluar - $sb->masuk - $sb->beli;
          $item = [
            "nama_pack" => $mutasi->nama_pack,
            "tanggal" => $sb->waktu,
            "masuk" => $sb->masuk,
            "keluar" => $sb->keluar,
            "sisa" => $stok,
            "beli" => $sb->beli,
            "harga" => $sb->harga,
          ];
          array_push($mutasiPack, $item);
        }
      } else {
        foreach ($mutasi->log_pack as $sb) {
          $item = [
            "nama_pack" => $mutasi->nama_pack,
            "tanggal" => $sb->waktu,
            "masuk" => $sb->masuk,
            "keluar" => $sb->keluar,
            "stok" => $sb->stok - $sb->keluar + $sb->masuk,
            "beli" => $sb->beli,
            "harga" => $sb->harga,
          ];
          array_push($mutasiPack, $item);
        }
      }


      return $mutasiPack;
    }
}
