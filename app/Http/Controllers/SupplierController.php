<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Supplier;
use App\StokBarang;

class SupplierController extends Controller
{
  public function get_all()
  {
    return response(Supplier::with("stok_barang")->get());
  }

  public function get($limit = 10, $offset = 0)
  {
    $supplier = Supplier::take($limit)->skip($offset)->get();
    $list = array();

    foreach ($supplier as $b) {
      $stok_barang = array();
      foreach ($b->stok_barang as $sb) {
        $itemStok = [
          "id_barang" => $sb->id_barang,
          "nama_barang" => $sb->barang->nama_barang,
          "satuan" => $sb->barang->satuan,
          "stok" => $sb->stok
        ];

        array_push($stok_barang, $itemStok);
      }

      $item = [
        "id_supplier" => $b->id_supplier,
        "nama_supplier" => $b->nama_supplier,
        "alamat" => $b->alamat,
        "kontak" => $b->kontak,
        "stok_barang" => $stok_barang
      ];

      array_push($list, $item);
    }
    return response([
      "supplier" => $list,
      "count" => Supplier::count()
    ]);
  }

  public function find(Request $request, $limit = 10, $offset = 0)
  {
    $find = $request->find;
    $supplier = Supplier::where("nama_supplier","like","%$find%")
    ->orWhere("alamat", "like","%$find%");
    $count = $supplier->count();
    $list = array();

    foreach ($supplier->take($limit)->skip($offset)->get() as $b) {
      $stok_barang = array();
      foreach ($b->stok_barang as $sb) {
        $itemStok = [
          "id_barang" => $sb->id_barang,
          "nama_barang" => $sb->barang->nama_barang,
          "satuan" => $sb->barang->satuan,
          "stok" => $sb->stok
        ];

        array_push($stok_barang, $itemStok);
      }

      $item = [
        "id_supplier" => $b->id_supplier,
        "nama_supplier" => $b->nama_supplier,
        "alamat" => $b->alamat,
        "kontak" => $b->kontak,
        "stok_barang" => $stok_barang
      ];

      array_push($list, $item);
    }
    return response([
      "supplier" => $list,
      "count" => $count
    ]);
  }

  public function store(Request $request)
  {
    try {
      $supplier = new Supplier();
      $supplier->id_supplier = "IDS".rand(1,1000);
      $supplier->nama_supplier = $request->nama_supplier;
      $supplier->alamat = $request->alamat;
      $supplier->kontak = $request->kontak;
      $supplier->save();
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
      $supplier = Supplier::where("id_supplier", $request->id_supplier)->first();
      $supplier->nama_supplier = $request->nama_supplier;
      $supplier->alamat = $request->alamat;
      $supplier->kontak = $request->kontak;
      $supplier->save();

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
      Supplier::where("id_supplier", $id)->delete();
      StokBarang::where("id_supplier", $id)->delete();

      return response([
        "message" => "Data barang berhasil dihapus"
      ]);
    } catch (\Exception $e) {
      return response([
        "message" => $e->getMessage()
      ]);
    }
  }
}
?>
