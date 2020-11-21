<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Pack;
use App\PackBarang;

class PackController extends Controller
{
  public function get_all()
  {
    $pack = Pack::all();
    $list = array();

    foreach ($pack as $b) {
      $item = [
        "id_pack" => $b->id_pack,
        "nama_pack" => $b->nama_pack,
        "keterangan" => $b->keterangan,
        "stok" => $b->stok,
        "harga" => $b->harga
      ];
      array_push($list, $item);
    }

    return response([
      "pack" => $list,
      "count" => Pack::count()
    ]);
  }

  public function get($limit = 10, $offset = 0)
  {
    $pack = Pack::take($limit)->skip($offset);
    $list = array();

    foreach ($pack as $b) {
      $item = [
        "id_pack" => $b->id_pack,
        "nama_pack" => $b->nama_pack,
        "keterangan" => $b->keterangan,
        "stok" => $b->stok,
        "harga" => $b->harga
      ];
      array_push($list, $item);
    }

    return response([
      "pack" => $list,
      "count" => Pack::count()
    ]);
  }

  public function store(Request $request)
  {
    try {
      $pack = new Pack();
      $pack->id_pack = "IDP".rand(1,1000);
      $pack->nama_pack = $request->nama_pack;
      $pack->keterangan = $request->keterangan;
      $pack->harga = $request->harga;
      $pack->stok = $request->stok;
      $pack->save();

      return response([
        "message" => "Data pack berhasil ditambahkan"
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
      $pack = Pack::where("id_pack", $request->id_pack)->first();
      $pack->nama_pack = $request->nama_pack;
      $pack->keterangan = $request->keterangan;
      $pack->stok = $request->stok;
      $pack->harga = $request->harga;
      $pack->save();

      return response([
        "message" => "Data pack berhasil diubah"
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
      Pack::where("id_pack", $id)->delete();
      PackBarang::where("id_pack", $id)->delete();

      return response([
        "message" => "Data pack berhasil dihapus"
      ]);
    } catch (\Exception $e) {
      return response([
        "message" => $e->getMessage()
      ]);
    }

  }
}
?>
