<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Users;
use App\Orders;
use App\Pack;
use App\KembaliPack;
use App\SetorUang;
use App\Barang;
use App\Bill;
use DB;

class DriverController extends Controller
{
  public function get_all()
  {
    $list = array();
    $driver = Users::where("id_level","4")->where("status","1");

    foreach ($driver->get() as $o) {
      $item = [
        "id_users" => $o->id_users,
        "nama" => $o->nama,
        "alamat" => $o->alamat,
        "contact" => $o->contact,
        "email" => $o->email,
        "username" => $o->username,
        "password" => Crypt::decrypt($o->password),
        "id_level" => $o->id_level,
        "status" => $o->status,
        "image" => $o->image,
        "nama_level" => $o->level->nama_level
      ];
      array_push($list, $item);
    }

    return response([
      "driver" => $list,
      "count" => $driver->count()
    ]);
  }

  public function get($limit = 5, $offset = 0)
  {
    $list = array();
    $driver = Users::where("id_level","4")->where("status","1")
    ->take($limit)->skip($offset)->get();
    $count = Users::where("id_level","4")->where("status","1")->count();

    foreach ($driver as $o) {
      $item = [
        "id_users" => $o->id_users,
        "nama" => $o->nama,
        "alamat" => $o->alamat,
        "contact" => $o->contact,
        "email" => $o->email,
        "username" => $o->username,
        "password" => Crypt::decrypt($o->password),
        "id_level" => $o->id_level,
        "status" => $o->status,
        "image" => $o->image,
        "nama_level" => $o->level->nama_level
      ];
      array_push($list, $item);
    }

    return response([
      "driver" => $list,
      "count" => $count
    ]);
  }

  public $find;
  public function find(Request $request, $limit = 5, $offset = 0)
  {
    $this->find = $request->find;
    $list = array();
    $driver = Users::where(function($query){
      $query->where("id_level","4")->where("status","1");
    })
    ->where(function($query){
      $find = $this->find;
      $query->where("id_users","like","%$find%")
      ->orWhere("nama","like","%$find%")
      ->orWhere("alamat","like","%$find%")
      ->orWhere("contact","like","%$find%")
      ->orWhere("email","like","%$find%")
      ->orWhere("username","like","%$find%");
    });
    $count = $driver->count();

    foreach ($driver->take($limit)->skip($offset)->get() as $o) {
      $item = [
        "id_users" => $o->id_users,
        "nama" => $o->nama,
        "alamat" => $o->alamat,
        "contact" => $o->contact,
        "email" => $o->email,
        "username" => $o->username,
        "password" => Crypt::decrypt($o->password),
        "id_level" => $o->id_level,
        "status" => $o->status,
        "image" => $o->image,
        "nama_level" => $o->level->nama_level
      ];
      array_push($list, $item);
    }

    return response([
      "driver" => $list,
      "count" => $count
    ]);
  }

  public function store(Request $request)
  {
    try {
      $id = "IDU".rand(1,100000);
      $exists = Users::where("id_users", $id)->count();
      while ($exists > 0) {
        $id = "IDU".rand(1,100000);
        $exists = Users::where("id_users", $id)->count();
      }

      $driver = new Users();
      $driver->id_users= $id;
      $driver->nama = $request->nama;
      $driver->alamat = $request->alamat;
      $driver->contact = $request->contact;
      $driver->email = $request->email;
      $driver->username = $request->username;
      $driver->password = Crypt::encrypt($request->password);
      $driver->status = $request->status;
      $driver->id_level = $request->id_level;
      $file = $request->image;
      $fileName = time().".".$file->extension();
      $driver->image = $fileName;
      $driver->save();
      $request->file('image')->move(storage_path('image'), $fileName);
      return response([
        "message" => "Data driver berhasil ditambahkan"
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
      $driver = Users::where("id_users", $request->id_users)->first();
      if ($request->hasFile("image")) {
        $path = storage_path("image")."/".$driver->image;
        if (file_exists($path)) {
          unlink($path);
        }
        $file = $request->image;
        $fileName = time().".".$file->extension();
        $driver->image = $fileName;
        $request->file('image')->move(storage_path('image'), $fileName);
      }
      $driver->nama = $request->nama;
      $driver->alamat = $request->alamat;
      $driver->contact = $request->contact;
      $driver->email = $request->email;
      $driver->username = $request->username;
      $driver->password = Crypt::encrypt($request->password);
      $driver->status = $request->status;
      $driver->id_level = $request->id_level;
      $driver->save();
      return response([
        "message" => "Data driver berhasil diubah"
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
      $driver = Users::where("id_users", $id)->first();
      $driver->status = 0;
      $driver->save();
      return response([
        "message" => "Data driver berhasil dihapus"
      ]);
    } catch (\Exception $e) {
      return response([
        "message" => $e->getMessage()
      ]);
    }
  }

  public function auth(Request $request)
  {
    $username = $request->username;
    $password = $request->password;
    $owner = Users::where("id_level","4")->where("username", $username);
    if ($owner->count() > 0) {
      $o = $owner->first();
      if (Crypt::decrypt($o->password) == $password) {
        $token = str_random(40);
        $o->token = $token;
        $o->save();
        return response([
          "logged" => true,
          "token" => $token
        ]);
      }else{
        return response([
          "logged" => false
        ]);
      }
    } else {
      return response([
        "logged" => false
      ]);
    }
  }

  public function check(Request $request)
  {
    try {
      $token = $request->token;
      $check = Users::where("id_level","4")->where("token",$token)->count();
      if ($check > 0) {
        return response([
          "auth" => true,
          "driver" => Users::where("id_level","4")->where("token",$token)->first()
        ]);
      }else{
        return response(["auth" => false]);
      }
    } catch (\Exception $e) {
      return response(["auth" => false]);
    }
  }

  public $driver;
  public function dashboard(Request $request)
  {
    // try {
    //
    // } catch (\Exception $e) {
    //   return response(["error" => $e->getMessage()]);
    // }
    $token = $request->token;
    $this->driver = Users::where("id_level","4")->where("token",$token)->first();
    $kembaliPack = Pack::with(["kembali_pack" => function($query){
      $query->select("id_pack", DB::raw("sum(jumlah) as jumlah"))
      ->where("id_users", $this->driver->id_users)->groupBy("id_pack");
    }])->get();
    $setorUang = SetorUang::where("id_users", $this->driver->id_users)->sum("nominal");
    $kirimOrder = Orders::where("id_sopir", $this->driver->id_users)
    ->where("id_status_orders","3")->count();
    $deliveredOrder = Orders::where("id_sopir", $this->driver->id_users)
    ->where("id_status_orders","4")->count();

    // $prepareBarang = Orders::with(["log_get_supplier" => function($query){
    //   $query->select("id_orders","id_barang","id_supplier",DB::raw("sum(jumlah) as jumlah"))
    //   ->groupBy(["id_barang","id_supplier"]);
    // },"log_get_supplier.barang","log_get_supplier.supplier"])
    // ->where("id_sopir", $this->driver->id_users)
    // ->where("id_status_orders","3")->get();

    $prepareBarang = Barang::with(["log_get_supplier" => function($query){
      $query->select("id_barang","id_supplier",DB::raw("sum(jumlah) as jumlah"))
      ->whereIn("id_orders", function($query){
        $query->select("id_orders")->from("orders")
        ->where("id_sopir", $this->driver->id_users)
        ->where("id_status_orders","3");
      })
      ->groupBy("id_supplier");
    },"log_get_supplier.supplier"])->get();

    $preparePack = Pack::with(["detail_orders" => function($query){
      $query->select("id_pack", DB::raw("sum(jumlah_pack) as jumlah"))
      ->whereIn("id_orders", function($query){
        $query->select("id_orders")->from("orders")
        ->where("id_sopir", $this->driver->id_users)
        ->where("id_status_orders","3");
      })->groupBy("id_pack");
    }])->get();

    $kembaliOrders = Barang::with(["detail_kembali_orders" => function($query){
      $query->select("id_barang",DB::raw("sum(jumlah_barang) as jumlah"))
      ->whereIn("id_kembali_orders", function($join){
        $join->select("id_kembali_orders")->from("kembali_orders")
        ->where("id_sopir", $this->driver->id_users);
      })->groupBy("id_barang");
    }])->get();
    return response([
      "users" => $this->driver,
      "kembali_pack" => $kembaliPack,
      "setor_uang" => $setorUang,
      "kirim_order" => $kirimOrder,
      "delivered_order" => $deliveredOrder,
      "prepare_barang" => $prepareBarang,
      "prepare_pack" => $preparePack,
      "kembali_orders" => $kembaliOrders
    ]);
  }

  public $id_users;
  public function kembali_pack($id_users)
  {
    $this->id_users = $id_users;
    $data = Users::where("id_level","5")->where("status","1")
    ->with(["kembali_pack" => function($query){
      $query->select("id_pembeli","id_pack", DB::raw("sum(jumlah) as jumlah"))
      ->where("id_users", $this->id_users)
      ->groupBy("id_pack");
    },"kembali_pack.pack"])
    ->get();

    return response($data);
  }

  public function store_kembali_pack(Request $request)
  {
    $kembali_pack = json_decode($request->kembali_pack);
    foreach ($kembali_pack as $kp) {
      $kembaliPack = new KembaliPack();
      $kembaliPack->id_kembali_pack = "KP".time().rand(1,1000);
      $kembaliPack->waktu = date("Y-m-d H:i:s");
      $kembaliPack->id_pack = $kp->id_pack;
      $kembaliPack->jumlah = $kp->jumlah;
      $kembaliPack->id_users = $request->id_users;
      $kembaliPack->id_pembeli = $request->id_pembeli;
      $kembaliPack->save();
    }

    return response(["message" => "Data setoran pack telah disimpan"]);
  }

  public function drop_kembali_pack($id_pembeli,$id_pack)
  {
    try {
      KembaliPack::where("id_pack", $id_pack)->where("id_pembeli", $id_pembeli)->delete();
      return response(["message" => "Data setoran pack telah dihapus"]);
    } catch (\Exception $e) {
      return response(["message" => $e->getMessage()]);
    }
  }

  public function setor_uang($id_users)
  {
    $this->id_users = $id_users;
    $data = Users::where("id_level","5")->where("status","1")
    ->with([
      "setor_uang", "setor_uang.orders", "setor_uang.orders.pembeli",
      "setor_uang.orders.sopir","setor_uang.orders.setor_tagihan"
    ])
    ->whereHas("setor_uang", function($query){
      $query->where("id_users",$this->id_users);
    })
    ->get();

    return response($data);
  }

  public function store_setor_uang(Request $request)
  {
    $setor_uang = json_decode($request->setor_uang);
    foreach ($setor_uang as $su) {
      // $su = id_orders
      $o = Orders::where("id_orders", $su)->first();
      $setorUang = new SetorUang();
      $setorUang->id_setor = "SU".time().rand(1,1000);
      $setorUang->waktu = date("Y-m-d H:i:s");
      $setorUang->id_users = $request->id_users;
      $setorUang->id_pembeli = $o->id_pembeli;
      $setorUang->nominal = $o->total_bayar-$o->down_payment;
      $setorUang->id_orders = $o->id_orders;
      $setorUang->save();

      $bill = Bill::where("id_orders", $su)->first();
      $bill->status = "0";
      $bill->save();
    }

    return response(["message" => "Data setoran pack telah disimpan"]);
  }

  public function drop_setor_uang($id_users, $id_pembeli)
  {
    try {
      $setor = SetorUang::where("id_users", $id_users)->where("id_pembeli", $id_pembeli)->get();
      foreach ($setor as $su) {
        $bill = Bill::where("id_orders", $su->id_orders)->first();
        $bill->status = "1";
        $bill->save();
      }
      SetorUang::where("id_users", $id_users)->where("id_pembeli", $id_pembeli)
      ->delete();
      return response(["message" => "Data setoran uang telah dihapus"]);
    } catch (\Exception $e) {
      return response(["message" => $e->getMessage()]);
    }
  }
}

 ?>
