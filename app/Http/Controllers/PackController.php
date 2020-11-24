<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Pack;
use App\PackBarang;
use DB;

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

  public $from;
  public $to;
  public $id_pembeli;
  public function mutasi_pack(Request $request, $id_pack)
  {
    $this->from = $request->from." 00:00:00";
    $this->to = $request->to." 23:59:59";

    if ($request->has("id_pembeli")) {
      $this->id_pembeli = $request->id_pembeli;
      $mutasi = Pack::where("id_pack",$id_pack)->with(["log_pack" => function($query){
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

    } else {
      $mutasi = Pack::where("id_pack",$id_pack)->with(["log_pack" => function($query){
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
    return response($mutasi);
  }
}
?>
