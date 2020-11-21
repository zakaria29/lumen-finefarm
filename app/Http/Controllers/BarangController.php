<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Barang;
use App\PackBarang;
use App\LogHargaBarang;

class BarangController extends Controller
{
  public function get_all()
  {
    $barang = Barang::all();
    $list = array();

    foreach ($barang as $b) {
      $pack = array();
      foreach ($b->pack as $p) {
        $itemPack = [
          "id_pack" => $p->id_pack,
          "nama_pack" => $p->pack->nama_pack,
          "keterangan" => $p->pack->keterangan,
          "harga" => $p->pack->harga,
          "kapasitas_kg" => $p->kapasitas_kg * 10,
          "kapasitas_butir" => ($p->kapasitas_butir == 0) ? 0 : 1 / $p->kapasitas_butir
        ];
        array_push($pack, $itemPack);
      }

      $logHarga = array();
      foreach ($b->log_harga as $l) {
        $itemHarga = [
          "waktu" => $l->waktu,
          "id_barang" => $l->id_barang,
          "harga" => $l->harga,
          "id_users" => $l->id_users,
          "nama" => $l->users->nama
        ];
        array_push($logHarga, $itemHarga);
      }

      $item = [
        "id_barang" => $b->id_barang,
        "nama_barang" => $b->nama_barang,
        "keterangan" => $b->keterangan,
        "satuan" => $b->satuan,
        "stok" => $b->stok->sum("stok"),
        "harga" => ($b->harga_barang->count()) ? $b->harga_barang->first()->harga : 0,
        "pack" => $pack,
        "log_harga_barang" => $logHarga
      ];

      array_push($list, $item);
    }

    return response([
      "barang" => $list,
      "count" => Barang::count()
    ]);
  }

  public function get($limit = 10, $offset = 0)
  {
    $barang = Barang::take($limit)->skip($offset);
    $list = array();

    foreach ($barang as $b) {
      $pack = array();
      foreach ($b->pack as $p) {
        $itemPack = [
          "id_pack" => $p->id_pack,
          "nama_pack" => $p->pack->nama_pack,
          "keterangan" => $p->pack->keterangan,
          "harga" => $p->pack->harga,
          "kapasitas_kg" => $p->kapasitas_kg * 10,
          "kapasitas_butir" => ($p->kapasitas_butir == 0) ? 0 : 1 / $p->kapasitas_butir
        ];
        array_push($pack, $itemPack);
      }

      $item = [
        "id_barang" => $b->id_barang,
        "nama_barang" => $b->nama_barang,
        "keterangan" => $b->keterangan,
        "satuan" => $b->satuan,
        "stok" => $b->stok,
        "harga" => $b->harga_barang->harga,
        "pack" => $pack
      ];

      array_push($list, $item);
    }

    return response([
      "barang" => $list,
      "count" => Barang::count()
    ]);
  }

  public function store(Request $request)
  {
    try {
      $barang = new Barang();
      $barang->id_barang = "IDB".rand(1,1000);
      $barang->nama_barang = $request->nama_barang;
      $barang->keterangan = $request->keterangan;
      $barang->satuan = $request->satuan;
      $barang->save();

      $packBarang = json_decode($request->pack_barang);
      foreach ($packBarang as $pb) {
        $pack = new PackBarang();
        $pack->id_barang = $barang->id_barang;
        $pack->id_pack = $pb->id_pack;
        $pack->kapasitas_kg = $pb->kapasitas_kg / 10;
        $pack->kapasitas_butir = ($pb->kapasitas_butir == 0) ? 0 : 1 / $pb->kapasitas_butir;

        $pack->save();
      }
      return response([
        "message" => "Data barang berhasil ditambahkan"
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
      $barang = Barang::where("id_barang", $request->id_barang)->first();
      $barang->nama_barang = $request->nama_barang;
      $barang->keterangan = $request->keterangan;
      $barang->satuan = $request->satuan;
      $barang->save();

      PackBarang::where("id_barang", $request->id_barang)->delete();
      $packBarang = json_decode($request->pack_barang);
      foreach ($packBarang as $pb) {
        $pack = new PackBarang();
        $pack->id_barang = $barang->id_barang;
        $pack->id_pack = $pb->id_pack;
        $pack->kapasitas_kg = $pb->kapasitas_kg / 10;
        $pack->kapasitas_butir = ($pb->kapasitas_butir == 0) ? 0 : 1 / $pb->kapasitas_butir;
        $pack->save();
      }

      return response([
        "message" => "Data barang berhasil diubah"
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
      Barang::where("id_barang", $id)->delete();
      PackBarang::where("id_barang", $id)->delete();

      return response([
        "message" => "Data barang berhasil dihapus"
      ]);
    } catch (\Exception $e) {
      return response([
        "message" => $e->getMessage()
      ]);
    }
  }

  public function update_harga(Request $request)
  {
    $log = new LogHargaBarang();
    $log->waktu = date("Y-m-d H:i:s");
    $log->id_barang = $request->id_barang;
    $log->harga = $request->harga;
    $log->id_users = $request->id_users;
    $log->save();

    return response([
      "message" => "Harga telah diupdate"
    ]);
  }
}
?>
