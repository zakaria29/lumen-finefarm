<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Supply;
use App\DetailSupply;
use App\LogStokBarang;
use App\StokBarang;
use App\LogUang;

/**
 *
 */
class SupplyController extends Controller
{
  public function store(Request $request)
  {
    try {
      $supply = new Supply();
      $supply->id_supply = "SUP".time().rand(1,1000);
      $supply->waktu = $request->waktu;
      $supply->id_supplier = $request->id_supplier;
      $supply->id_users = $request->id_users;
      $supply->total_bayar = $request->total_bayar;
      $supply->save();

      $logUang = new LogUang();
      $logUang->id_log_uang = $supply->id_supply;
      $logUang->waktu = $request->waktu;
      $logUang->nominal = $request->total_bayar;
      $logUang->status = "out";
      $logUang->event = "Supply barang";
      $logUang->id_users = $request->id_supplier;
      $logUang->save();

      $detail_supply = json_decode($request->detail_supply);
      foreach ($detail_supply as $ds) {
        $detail = new DetailSupply();
        $detail->id_supply = $supply->id_supply;
        $detail->id_barang = $ds->id_barang;
        $detail->harga_beli = $ds->harga_beli;
        $detail->jumlah_utuh = $ds->jumlah_utuh;
        $detail->jumlah_bentes = $ds->jumlah_bentes;
        $detail->jumlah_putih = $ds->jumlah_putih;
        $detail->jumlah_pecah = $ds->jumlah_pecah;
        $detail->jumlah_loss = $ds->jumlah_loss;
        $detail->save();

        $log = new LogStokBarang();
        $log->waktu = $request->waktu;
        $log->event = "Supply Barang";
        $log->id_supplier = $request->id_supplier;
        $log->id_users = $request->id_users;
        $log->id_barang = $ds->id_barang;
        $log->jumlah = $ds->jumlah_utuh;
        $log->id = $supply->id_supply;
        $log->status = "in";
        $log->loss = $ds->jumlah_loss;
        $log->save();

        $stokBarang = StokBarang::where("id_supplier", $request->id_supplier)
        ->where("id_barang", $ds->id_barang);
        if ($stokBarang->count() > 0) {
          $stok = $stokBarang->first();
          StokBarang::where("id_supplier", $request->id_supplier)
          ->where("id_barang", $ds->id_barang)
          ->update(["stok" => $stok->stok + $ds->jumlah_utuh]);
        }else{
          $stok = new StokBarang();
          $stok->id_supplier = $request->id_supplier;
          $stok->id_barang = $ds->id_barang;
          $stok->stok = $ds->jumlah_utuh;
          $stok->save();
        }


        if ($ds->jumlah_putih > 0) {
          $stokBarang = StokBarang::where("id_supplier", $request->id_supplier)
          ->where("id_barang", "IDB711");
          if ($stokBarang->count() > 0) {
            $stok = $stokBarang->first();
            StokBarang::where("id_supplier", $request->id_supplier)
            ->where("id_barang", "IDB711")
            ->update(["stok" => $stok->stok+$ds->jumlah_putih]);
          }else{
            $stok = new StokBarang();
            $stok->id_supplier = $request->id_supplier;
            $stok->id_barang = "IDB711";
            $stok->stok = $ds->jumlah_putih;
            $stok->save();
          }
        }
        if ($ds->jumlah_bentes > 0) {
          $stokBarang = StokBarang::where("id_supplier", $request->id_supplier)
          ->where("id_barang", "IDB443");
          if ($stokBarang->count() > 0) {
            $stok = $stokBarang->first();
            StokBarang::where("id_supplier", $request->id_supplier)
            ->where("id_barang", "IDB443")
            ->update(["stok" => $stok->stok+$ds->jumlah_bentes]);
          }else{
            $stok = new StokBarang();
            $stok->id_supplier = $request->id_supplier;
            $stok->id_barang = "IDB443";
            $stok->stok = $ds->jumlah_bentes;
            $stok->save();
          }
        }
      }
      return response([
        "message" => "Data supply berhasil di ditambahkan"
      ]);
    } catch (\Exception $e) {
      return response([
        "message" => $e->getMessage()
      ]);
    }
  }

  public function update(Request $request)
  {
    try {
      $supply = Supply::where("id_supply", $request->id_supply)->first();
      // $supply->waktu = date("Y-m-d H:i:s");
      $supply->id_supplier = $request->id_supplier;
      $supply->id_users = $request->id_users;
      $supply->total_bayar = $request->total_bayar;
      $supply->save();

      $logUang = LogUang::where("id_log_uang", $request->id_supply)->first();
      // $logUang->waktu = date("Y-m-d H:i:s");
      $logUang->nominal = $request->total_bayar;
      $logUang->status = "out";
      $logUang->event = "Supply barang";
      $logUang->id_users = $request->id_supplier;
      $logUang->save();

      $detail_supply = DetailSupply::where("id_supply", $request->id_supply)->get();
      foreach ($detail_supply as $ds) {
        $stok = StokBarang::where("id_supplier", $request->id_supplier)
        ->where("id_barang", $ds->id_barang)->first();
        StokBarang::where("id_supplier", $request->id_supplier)
        ->where("id_barang", $ds->id_barang)
        ->update(["stok" => $stok->stok - $ds->jumlah_utuh]);

        if ($ds->jumlah_putih > 0) {
          $stok = StokBarang::where("id_supplier", $request->id_supplier)
          ->where("id_barang", "IDB711")->first();
          StokBarang::where("id_supplier", $request->id_supplier)
          ->where("id_barang", "IDB711")
          ->update(["stok" => $stok->stok - $ds->jumlah_putih]);
        }
        if ($ds->jumlah_bentes > 0) {
          $stok = StokBarang::where("id_supplier", $request->id_supplier)
          ->where("id_barang", "IDB443")->first();
          StokBarang::where("id_supplier", $request->id_supplier)
          ->where("id_barang", "IDB443")
          ->update(["stok" => $stok->stok - $ds->jumlah_bentes]);
        }
      }

      DetailSupply::where("id_supply", $request->id_supply)->delete();
      LogStokBarang::where("id", $request->id_supply)->delete();
      $detail_supply = json_decode($request->detail_supply);
      foreach ($detail_supply as $ds) {
        $detail = new DetailSupply();
        $detail->id_supply = $supply->id_supply;
        $detail->id_barang = $ds->id_barang;
        $detail->harga_beli = $ds->harga_beli;
        $detail->jumlah_utuh = $ds->jumlah_utuh;
        $detail->jumlah_bentes = $ds->jumlah_bentes;
        $detail->jumlah_putih = $ds->jumlah_putih;
        $detail->jumlah_pecah = $ds->jumlah_pecah;
        $detail->jumlah_loss = $ds->jumlah_loss;
        $detail->save();

        $log = new LogStokBarang();
        $log->waktu = $supply->waktu;
        $log->event = "Supply Barang";
        $log->id_supplier = $request->id_supplier;
        $log->id_users = $request->id_users;
        $log->id_barang = $ds->id_barang;
        $log->jumlah = $ds->jumlah_utuh;
        $log->id = $supply->id_supply;
        $log->status = "in";
        $log->loss = $ds->jumlah_loss;
        $log->save();

        $stokBarang = StokBarang::where("id_supplier", $request->id_supplier)
        ->where("id_barang", $ds->id_barang);
        if ($stokBarang->count() > 0) {
          $stok = $stokBarang->first();
          StokBarang::where("id_supplier", $request->id_supplier)
          ->where("id_barang", $ds->id_barang)
          ->update(["stok" => $stok->stok + $ds->jumlah_utuh]);
        }else{
          $stok = new StokBarang();
          $stok->id_supplier = $request->id_supplier;
          $stok->id_barang = $ds->id_barang;
          $stok->stok = $ds->jumlah_utuh;
          $stok->save();
        }


        if ($ds->jumlah_putih > 0) {
          $stokBarang = StokBarang::where("id_supplier", $request->id_supplier)
          ->where("id_barang", "IDB711");
          if ($stokBarang->count() > 0) {
            $stok = $stokBarang->first();
            StokBarang::where("id_supplier", $request->id_supplier)
            ->where("id_barang", "IDB711")
            ->update(["stok" => $stok->stok + $ds->jumlah_putih]);
          }else{
            $stok = new StokBarang();
            $stok->id_supplier = $request->id_supplier;
            $stok->id_barang = "IDB711";
            $stok->stok = $ds->jumlah_putih;
            $stok->save();
          }
        }
        if ($ds->jumlah_bentes > 0) {
          $stokBarang = StokBarang::where("id_supplier", $request->id_supplier)
          ->where("id_barang", "IDB443");
          if ($stokBarang->count() > 0) {
            $stok = $stokBarang->first();
            StokBarang::where("id_supplier", $request->id_supplier)
            ->where("id_barang", "IDB443")
            ->update(["stok" => $stok->stok + $ds->jumlah_bentes]);
          }else{
            $stok = new StokBarang();
            $stok->id_supplier = $request->id_supplier;
            $stok->id_barang = "IDB443";
            $stok->stok = $ds->jumlah_bentes;
            $stok->save();
          }
        }
      }
      return response([
        "message" => "Data supply berhasil di diubah"
      ]);
    } catch (\Exception $e) {
      return response([
        "message" => $e->getMessage()
      ]);
    }
  }

  public function drop($id)
  {
    try {
      $supply = Supply::where("id_supply", $id)->first();
      $detail_supply = DetailSupply::where("id_supply", $id)->get();
      foreach ($detail_supply as $ds) {
        $stok = StokBarang::where("id_supplier", $supply->id_supplier)
        ->where("id_barang", $ds->id_barang)->first();
        StokBarang::where("id_supplier", $supply->id_supplier)
        ->where("id_barang", $ds->id_barang)
        ->update(["stok" => $stok->stok - $ds->jumlah_utuh]);

        /* start kondisi memaksakan */
        if ($ds->jumlah_putih > 0) {
          $stok = StokBarang::where("id_supplier", $supply->id_supplier)
          ->where("id_barang", "IDB711")->first();
          StokBarang::where("id_supplier", $supply->id_supplier)
          ->where("id_barang", "IDB711")
          ->update(["stok" => $stok->stok - $ds->jumlah_putih]);
        }
        if ($ds->jumlah_bentes > 0) {
          $stok = StokBarang::where("id_supplier", $supply->id_supplier)
          ->where("id_barang", "IDB443")->first();
          StokBarang::where("id_supplier", $supply->id_supplier)
          ->where("id_barang", "IDB443")
          ->update(["stok" => $stok->stok - $ds->jumlah_bentes]);
        }
        /* end kondisi memaksakan */
      }
      LogStokBarang::where("id", $id)->delete();
      LogUang::where("id_log_uang", $id)->delete();
      DetailSupply::where("id_supply", $id)->delete();
      Supply::where("id_supply", $id)->delete();

      return response([
        "message" => "Data supply berhasil dihapus"
      ]);
    } catch (\Exception $e) {
      return response([
        "message" => $e->getMessage()
      ]);
    }
  }

  public function get_all()
  {
    $supply = Supply::all();
    $list = array();

    foreach ($supply as $s) {
      $detail = array();
      foreach ($s->detail_supply as $ds) {
        $itemDetail = [
          "id_barang" => $ds->id_barang,
          "nama_barang" => $ds->barang->nama_barang,
          "harga_beli" => $ds->harga_beli,
          "jumlah_utuh" => $ds->jumlah_utuh,
          "jumlah_bentes" => $ds->jumlah_bentes,
          "jumlah_putih" => $ds->jumlah_putih,
          "jumlah_pecah" => $ds->jumlah_pecah,
          "jumlah_loss" => $ds->jumlah_loss
        ];
        array_push($detail, $itemDetail);
      }
      $item = [
        "id_supply" => $s->id_supply,
        "waktu" => $s->waktu,
        "id_supplier" => $s->id_supplier,
        "nama_supplier" => $s->supplier->nama_supplier,
        "id_users" => $s->id_users,
        "nama" => $s->users->nama,
        "total_bayar" => $s->total_bayar,
        "detail_supply" => $detail
      ];
      array_push($list, $item);

    }

    /* start rekap telur yang disupply */
    $rekap = DetailSupply::join("supply", "supply.id_supply","=","detail_supply.id_supply")
    ->join("barang", "barang.id_barang","=","detail_supply.id_barang")
    ->selectRaw("detail_supply.*, barang.nama_barang, sum(jumlah_utuh) as total_utuh,
    sum(jumlah_bentes) as total_bentes, sum(jumlah_putih) as total_putih,
    sum(jumlah_pecah) as total_pecah, sum(jumlah_loss) as total_loss")
    ->groupBy("detail_supply.id_barang")->get();
    $rekap_telur = array();
    foreach ($rekap as $r) {
      $item = [
        "id_barang" => $r->id_barang,
        "nama_barang" => $r->nama_barang,
        "total_utuh" => $r->total_utuh,
        "total_bentes" => $r->total_bentes,
        "total_pecah" => $r->total_pecah,
        "total_putih" => $r->total_putih,
        "total_loss" => $r->total_loss,
      ];
      array_push($rekap_telur, $item);
    }
    /* end rekap telur yang disupply */

    return response([
      "supply" => $list,
      "count" => Supply::count(),
      "total" => Supply::sum("total_bayar"),
      "rekap_telur" => $rekap_telur
    ]);
  }

  public function get($id)
  {
    $supply = Supply::where("id_supply", $id)->get();
    $list = array();

    foreach ($supply as $s) {
      $detail = array();
      foreach ($s->detail_supply as $ds) {
        $itemDetail = [
          "id_barang" => $ds->id_barang,
          "nama_barang" => $ds->barang->nama_barang,
          "harga_beli" => $ds->harga_beli,
          "jumlah_utuh" => $ds->jumlah_utuh,
          "jumlah_bentes" => $ds->jumlah_bentes,
          "jumlah_putih" => $ds->jumlah_putih,
          "jumlah_pecah" => $ds->jumlah_pecah,
          "jumlah_loss" => $ds->jumlah_loss
        ];
        array_push($detail, $itemDetail);
      }
      $item = [
        "id_supply" => $s->id_supply,
        "waktu" => $s->waktu,
        "id_supplier" => $s->id_supplier,
        "nama_supplier" => $s->supplier->nama_supplier,
        "id_users" => $s->id_users,
        "nama" => $s->users->nama,
        "total_bayar" => $s->total_bayar,
        "detail_supply" => $detail
      ];
      array_push($list, $item);

    }

    /* start rekap telur yang disupply */
    $rekap = DetailSupply::join("supply", "supply.id_supply","=","detail_supply.id_supply")
    ->join("barang", "barang.id_barang","=","detail_supply.id_barang")
    ->selectRaw("detail_supply.*, barang.nama_barang, sum(jumlah_utuh) as total_utuh,
    sum(jumlah_bentes) as total_bentes, sum(jumlah_putih) as total_putih,
    sum(jumlah_pecah) as total_pecah, sum(jumlah_loss) as total_loss")
    ->groupBy("detail_supply.id_barang")->get();
    $rekap_telur = array();
    foreach ($rekap as $r) {
      $item = [
        "id_barang" => $r->id_barang,
        "nama_barang" => $r->nama_barang,
        "total_utuh" => $r->total_utuh,
        "total_bentes" => $r->total_bentes,
        "total_pecah" => $r->total_pecah,
        "total_putih" => $r->total_putih,
        "total_loss" => $r->total_loss,
      ];
      array_push($rekap_telur, $item);
    }
    /* end rekap telur yang disupply */

    return response([
      "supply" => $list,
      // "count" => Supply::count(),
      // "total" => Supply::sum("total_bayar"),
      // "rekap_telur" => $rekap_telur
    ]);
  }

  public $find = "";
  public $from = "";
  public $to = "";
  public function find(Request $request, $limit = 5, $offset = 0)
  {
    $this->from = $request->from." 00:00:00";
    $this->to = $request->to." 23:59:59";
    $this->find = $request->find;

    $sup = Supply::join("users","users.id_users","=","supply.id_users")
    ->join("supplier","supplier.id_supplier","=","supply.id_supplier")
    ->whereBetween("waktu", [$this->from, $this->to])
    ->where(function($query){
      $find = $this->find;
      $query->where("supply.id_supplier","like","%$find%")
      ->orWhere("supply.id_users","like","%$find%")
      ->orWhere("nama_supplier","like","%$find%")
      ->orWhere("nama","like","%$find%")
      ->orderBy("waktu", "desc");
    });

    $count = $sup->count();
    $total = 0;
    foreach ($sup->get() as $s) {
      $total += $s->total_bayar;
    }
    $list = array();
    $supply = $sup->take($limit)->skip($offset)->get();
    foreach ($supply as $s) {
      $detail = array();
      foreach ($s->detail_supply as $ds) {
        $itemDetail = [
          "id_barang" => $ds->id_barang,
          "nama_barang" => $ds->barang->nama_barang,
          "harga_beli" => $ds->harga_beli,
          "jumlah_utuh" => $ds->jumlah_utuh,
          "jumlah_bentes" => $ds->jumlah_bentes,
          "jumlah_putih" => $ds->jumlah_putih,
          "jumlah_pecah" => $ds->jumlah_pecah,
          "jumlah_loss" => $ds->jumlah_loss
        ];
        array_push($detail, $itemDetail);
      }
      $item = [
        "id_supply" => $s->id_supply,
        "waktu" => $s->waktu,
        "id_supplier" => $s->id_supplier,
        "nama_supplier" => $s->nama_supplier,
        "id_users" => $s->id_users,
        "nama" => $s->nama,
        "total_bayar" => $s->total_bayar,
        "detail_supply" => $detail
      ];
      array_push($list, $item);

    }

    /* start rekap telur yang disupply */
    $find = $this->find;
    $rekap = DetailSupply::join("supply", "supply.id_supply","=","detail_supply.id_supply")
    ->join("barang", "barang.id_barang","=","detail_supply.id_barang")
    ->join("supplier","supply.id_supplier","=","supplier.id_supplier")
    ->join("users","supply.id_users","=","users.id_users")
    ->selectRaw("detail_supply.*, barang.nama_barang, sum(jumlah_utuh) as total_utuh,
    sum(jumlah_bentes) as total_bentes, sum(jumlah_putih) as total_putih,
    sum(jumlah_pecah) as total_pecah, sum(jumlah_loss) as total_loss")
    ->where(function($query){
      $from = $this->from;
      $to = $this->to;
      $query->whereBetween("waktu", [$from, $to]);
    })
    ->orwhere("supply.id_supplier","like","%$find%")
    ->orWhere("supply.id_users","like","%$find%")
    ->orWhere("supplier.nama_supplier","like","%$find%")
    ->orWhere("users.nama","like","%$find%")
    ->groupBy("detail_supply.id_barang")->get();
    $rekap_telur = array();
    foreach ($rekap as $r) {
      $item = [
        "id_barang" => $r->id_barang,
        "nama_barang" => $r->nama_barang,
        "total_utuh" => $r->total_utuh,
        "total_bentes" => $r->total_bentes,
        "total_pecah" => $r->total_pecah,
        "total_putih" => $r->total_putih,
        "total_loss" => $r->total_loss,
      ];
      array_push($rekap_telur, $item);
    }
    /* end rekap telur yang disupply */

    return response([
      "supply" => $list,
      "count" => $count,
      "total" => $total,
      "rekap_telur" => $rekap_telur,
    ]);
  }
}

 ?>
