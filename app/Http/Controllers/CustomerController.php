<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Users;
use App\GroupCustomer;
use App\Orders;
use App\Bill;
use App\TanggunganPack;
use App\TanggunganPembayaran;
use App\LockPackBarang;
use App\PackBarang;
class CustomerController extends Controller
{
  public function only_customer()
  {
    $customer = Users::where("id_level","5")->get();
    return response(["customer" => $customer]);
  }
  public function get_all()
  {
    $list = array();
    $customer = Users::where("id_level","5")->where("status","1");

    foreach ($customer->get() as $o) {
      $tanggungan_pack = array();
      foreach ($o->tanggungan_pack as $tp) {
        $itemPack = [
          "id_pack" => $tp->id_pack,
          "nama_pack" => $tp->pack->nama_pack,
          "jumlah" => $tp->jumlah
        ];
        array_push($tanggungan_pack, $itemPack);
      }

      $lock = array();
      foreach ($o->lock_pack_barang as $l) {
        $itemLock = [
          "id_users" => $l->id_users,
          "id_barang" => $l->id_barang,
          "nama_barang" => $l->barang->nama_barang,
          "id_pack" => $l->id_pack,
          "nama_pack" => $l->pack->nama_pack,
          "harga" => $l->harga,
          "kapasitas_kg" => $l->kapasitas_kg,
          "kapasitas_butir" => $l->kapasitas_butir
        ];
        array_push($lock, $itemLock);
      }
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
        "nama_level" => $o->level->nama_level,
        "margin" => $o->margin,
        "nama_instansi" => $o->nama_instansi,
        "bidang_usaha" => $o->bidang_usaha,
        "id_group_customer" => $o->id_group_customer,
        "nama_group_customer" => $o->group_customer->nama_group_customer,
        "margin_group" => $o->group_customer->margin,
        "jatuh_tempo" => $o->jatuh_tempo,
        "inisial" => $o->inisial,
        // "tanggungan_pembayaran" =>
        // $o->tanggungan_pembayaran->count() ? $o->tanggungan_pembayaran->nominal : "0",
        "tanggungan_pack" => $tanggungan_pack,
        "orders" => $o->orders,
        "lock_pack_barang" => $lock
      ];
      array_push($list, $item);
    }

    return response([
      "customer" => $list,
      "count" => $customer->count()
    ]);
  }

  public function get($limit = 5, $offset = 0)
  {
    $list = array();
    $customer = Users::where("id_level","5")->where("status","1")
    ->take($limit)->skip($offset)->get();
    $count = Users::where("id_level","5")->where("status","1")->count();

    foreach ($customer as $o) {
      $tanggungan_pack = array();
      foreach ($o->tanggungan_pack as $tp) {
        $itemPack = [
          "id_pack" => $tp->id_pack,
          "nama_pack" => $tp->pack->nama_pack,
          "jumlah" => $tp->jumlah
        ];
        array_push($tanggungan_pack, $itemPack);
      }

      $lock = array();
      foreach ($o->lock_pack_barang as $l) {
        $itemLock = [
          "id_users" => $l->id_users,
          "id_barang" => $l->id_barang,
          "nama_barang" => $l->barang->nama_barang,
          "id_pack" => $l->id_pack,
          "nama_pack" => $l->pack->nama_pack,
          "harga" => $l->harga,
          "kapasitas_kg" => $l->kapasitas_kg,
          "kapasitas_butir" => $l->kapasitas_butir
        ];
        array_push($lock, $itemLock);
      }

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
        "nama_level" => $o->level->nama_level,
        "margin" => $o->margin,
        "nama_instansi" => $o->nama_instansi,
        "bidang_usaha" => $o->bidang_usaha,
        "id_group_customer" => $o->id_group_customer,
        "nama_group_customer" => $o->group_customer->nama_group_customer,
        "margin_group" => $o->group_customer->margin,
        "jatuh_tempo" => $o->jatuh_tempo,
        "inisial" => $o->inisial,
        // "tanggungan_pembayaran" => $o->tanggungan_pembayaran->nominal,
        "tanggungan_pack" => $tanggungan_pack,
        "lock_pack_barang" => $lock
      ];
      array_push($list, $item);
    }

    return response([
      "customer" => $list,
      "count" => $count
    ]);
  }

  public $find;
  public function find(Request $request, $limit = 5, $offset = 0)
  {
    $this->find = $request->find;
    $list = array();
    $customer = Users::where(function($query){
      $query->where("id_level","5")->where("status","1");
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
    $count = $customer->count();

    foreach ($customer->take($limit)->skip($offset)->get() as $o) {
      $tanggungan_pack = array();
      foreach ($o->tanggungan_pack as $tp) {
        $itemPack = [
          "id_pack" => $tp->id_pack,
          "nama_pack" => $tp->pack->nama_pack,
          "jumlah" => $tp->jumlah
        ];
        array_push($tanggungan_pack, $itemPack);
      }

      $lock = array();
      foreach ($o->lock_pack_barang as $l) {
        $itemLock = [
          "id_users" => $l->id_users,
          "id_barang" => $l->id_barang,
          "nama_barang" => $l->barang->nama_barang,
          "id_pack" => $l->id_pack,
          "nama_pack" => $l->pack->nama_pack,
          "harga" => $l->harga,
          "kapasitas_kg" => $l->kapasitas_kg,
          "kapasitas_butir" => $l->kapasitas_butir
        ];
        array_push($lock, $itemLock);
      }

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
        "nama_level" => $o->level->nama_level,
        "margin" => $o->margin,
        "nama_instansi" => $o->nama_instansi,
        "bidang_usaha" => $o->bidang_usaha,
        "id_group_customer" => $o->id_group_customer,
        "nama_group_customer" => $o->group_customer->nama_group_customer,
        "margin_group" => $o->group_customer->margin,
        "jatuh_tempo" => $o->jatuh_tempo,
        "inisial" => $o->inisial,
        // "tanggungan_pembayaran" => $o->tanggungan_pembayaran->nominal,
        "tanggungan_pack" => $tanggungan_pack,
        "lock_pack_barang" => $lock
      ];
      array_push($list, $item);
    }

    return response([
      "customer" => $list,
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

      $customer = new Users();
      $customer->id_users= $id;
      $customer->nama = $request->nama;
      $customer->alamat = $request->alamat;
      $customer->contact = $request->contact;
      $customer->email = $request->email;
      $customer->username = $request->username;
      $customer->password = Crypt::encrypt($request->password);
      $customer->status = $request->status;
      $customer->id_level = $request->id_level;
      $file = $request->image;
      $fileName = time().".".$file->extension();
      $customer->image = $fileName;
      $customer->margin = $request->margin;
      $customer->nama_instansi = $request->nama_instansi;
      $customer->bidang_usaha = $request->bidang_usaha;
      $customer->id_group_customer = $request->id_group_customer;
      $customer->jatuh_tempo = $request->jatuh_tempo;
      $customer->inisial = $request->inisial;
      $customer->save();
      $request->file('image')->move(storage_path('image'), $fileName);
      return response([
        "message" => "Data customer berhasil ditambahkan"
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
      $customer = Users::where("id_users", $request->id_users)->first();
      if ($request->hasFile("image")) {
        $path = storage_path("image")."/".$customer->image;
        if (file_exists($path)) {
          unlink($path);
        }
        $file = $request->image;
        $fileName = time().".".$file->extension();
        $customer->image = $fileName;
        $request->file('image')->move(storage_path('image'), $fileName);
      }
      $customer->nama = $request->nama;
      $customer->alamat = $request->alamat;
      $customer->contact = $request->contact;
      $customer->email = $request->email;
      $customer->username = $request->username;
      $customer->password = Crypt::encrypt($request->password);
      $customer->status = $request->status;
      $customer->id_level = $request->id_level;
      $customer->margin = $request->margin;
      $customer->nama_instansi = $request->nama_instansi;
      $customer->bidang_usaha = $request->bidang_usaha;
      $customer->id_group_customer = $request->id_group_customer;
      $customer->jatuh_tempo = $request->jatuh_tempo;
      $customer->inisial = $request->inisial;
      $customer->save();
      return response([
        "message" => "Data customer berhasil diubah"
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
      $customer = Users::where("id_users", $id)->first();
      $customer->status = 0;
      $customer->save();
      return response([
        "message" => "Data customer berhasil dihapus"
      ]);
    } catch (\Exception $e) {
      return response([
        "message" => $e->getMessage()
      ]);
    }
  }

  public function get_group_customer()
  {
    return response([
      "group_customer" => GroupCustomer::all()
    ]);
  }

  public function auth(Request $request)
  {
    $username = $request->username;
    $password = $request->password;
    $owner = Users::where("id_level","5")->where("username", $username);
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
      $check = Users::where("id_level","5")->where("token",$token)->count();
      if ($check > 0) {
        return response([
          "auth" => true,
          "customer" => Users::with(["group_customer","orders","lock_pack_barang"])
          ->where("id_level","5")->where("token",$token)->first()
        ]);
      }else{
        return response(["auth" => false]);
      }
    } catch (\Exception $e) {
      return response(["auth" => false]);
    }
  }

  public function dashboard(Request $request)
  {
    try {
      $token = $request->token;
      $users = Users::where("id_level","5")
      ->where("token",$token)
      ->with([
        "tanggungan_pack","tanggungan_pack.pack","unverified_bill",
        "tanggungan_pembayaran","orders",
      ])
      ->first();
      return response($users);
    } catch (\Exception $e) {
      return response(["error" => $e->getMessage()]);
    }
  }

  public function orders(Request $request, $id = null, $limit = null, $offset = null)
  {
    $this->find = $request->find;
    $orders = Orders::where("id_pembeli", $id)->with([
      "pembeli","users", "sopir","status_orders","log_orders",
      "log_orders.users","log_orders.status_orders","tagihan",
      "detail_orders","detail_orders.barang","detail_orders.pack"
    ])->where(function($q){
      $q->whereHas("detail_orders.barang", function($query){
        $query->where("nama_barang","like","%$this->find%");
      })
      ->orWhereHas("detail_orders.pack", function($query){
        $query->where("nama_pack","like","%$this->find%");
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

      if ($limit == null or $offset == null) {
        return [
          "count" => $orders->count(),
          "orders" => $orders->get(),
        ];
      }else{
        return [
          "count" => $orders->count(),
          "orders" => $orders->take($limit)->skip($offset)->get(),
        ];
      }
  }

  public function get_bill($id_users)
  {
    return response(
      Bill::where("id_users", $id_users)->where("status","1")
      ->with([
        "orders","orders.detail_orders","orders.detail_orders.barang",
        "orders.detail_orders.pack","orders.sopir","orders.pembeli",
        "orders.status_orders","orders.users"
      ])->get()
    );
  }

  public function tanggungan_pack()
  {
    return response(
      Users::where("id_level","5")->where("status","1")
      ->with(["tanggungan_pack" => function($query){
        $query->where("jumlah",">","0");
      },"tanggungan_pack.pack"])->get()
    );
  }

  public function find_tanggungan_pack(Request $request)
  {
    $this->find = $request->find;
    return response(
      Users::where("id_level","5")->where("status","1")
      ->with(["tanggungan_pack" => function($query){
        $query->where("jumlah",">","0");
      },"tanggungan_pack.pack" => function($query){
        $find = $this->find;
        // $query->where("nama_pack","like","%$find%");
      }])
      ->where("nama","like","%$this->find%")
      ->get()
    );
  }

  public function save_tanggungan_pack(Request $request)
  {
    $id_users = $request->id_users;
    $tanggungan_pack = json_decode($request->tanggungan_pack);
    TanggunganPack::where("id_users", $id_users)->delete();
    foreach ($tanggungan_pack as $t) {
      $tp = new TanggunganPack();
      $tp->id_users = $id_users;
      $tp->id_pack = $t->id_pack;
      $tp->jumlah = $t->jumlah;
      $tp->save();
    }
    return response(["message" => "Tanggungan Pack telah diubah"]);
  }

  public function tanggungan_pembayaran()
  {
    return response(
      Users::where("id_level","5")->where("status","1")
      ->with(["bill" => function($query){
        $query->where("status", "1");
      }])
      ->get()
    );
  }

  public function find_tanggungan_pembayaran(Request $request)
  {
    return response(
      Users::where("id_level","5")->where("status","1")
      ->with(["bill" => function($query){
        $query->where("status","<","2");
      }])
      ->where("nama","like","%$request->find%")
      ->get()
    );
  }

  public function store_lock_pack_barang(Request $request)
  {
    try {
      $id_users = $request->id_users;
      LockPackBarang::where("id_users", $id_users)->delete();
      $lock_pack_barang = json_decode($request->lock_pack_barang);
      foreach ($lock_pack_barang as $lpb) {
        $pb = PackBarang::where("id_barang", $lpb->id_barang)
        ->where("id_pack", $lpb->id_pack)->first();
        $lock = new LockPackBarang();
        $lock->id_users = $id_users;
        $lock->id_barang = $lpb->id_barang;
        $lock->id_pack = $lpb->id_pack;
        $lock->harga = $lpb->harga;
        $lock->kapasitas_kg = ($pb == null) ? "0" : $pb->kapasitas_kg;
        $lock->kapasitas_butir = ($pb == null) ? "0" : $pb->kapasitas_butir;
        $lock->save();
      }
      return response(["message" => "Data lock user berhasil disimpan"]);
    } catch (\Exception $e) {
      return response(["message" => $e->getMessage()]);
    }

  }

  public function edit_profil(Request $request)
  {
    try {
      if ($request->has("password")) {
        Users::where("id_users", $request->id_users)
        ->update([
          "nama" => $request->nama,
          "alamat" => $request->alamat,
          "contact" => $request->contact,
          "email" => $request->email,
          "username" => $request->username,
          "password" => Crypt::encrypt($request->password)
        ]);
      } else {
        Users::where("id_users", $request->id_users)
        ->update([
          "nama" => $request->nama,
          "alamat" => $request->alamat,
          "contact" => $request->contact,
          "email" => $request->email,
          "username" => $request->username
        ]);
      }

      return response(["message" => "Data berhasil diubah"]);
    } catch (\Exception $e) {
      return response(["message" => $e->getMessage()]);
    }
  }

  public function get_tanggungan($id_users)
  {
    $bill = Bill::where("id_users", $id_users)->where("status","1")->with(["orders"])->get();
    $tp = TanggunganPack::where("id_users", $id_users)->where("jumlah",">","0")->get();
    return response([
      "tanggungan_pembayaran" => $bill,
      "tanggungan_pack" => $tp
    ]);
  }

  public function reset_tanggungan(Request $request)
  {
    try {
      $id_users = $request->id_users;
      $tanggungan_pack = json_decode($request->tanggungan_pack);
      TanggunganPack::where("id_users", $id_users)->delete();
      foreach ($tanggungan_pack as $t) {
        $tp = new TanggunganPack();
        $tp->id_users = $id_users;
        $tp->id_pack = $t->id_pack;
        $tp->jumlah = $t->jumlah;
        $tp->save();
      }

      $tanggungan_pembayaran = json_decode($request->tanggungan_pembayaran);
      TanggunganPembayaran::where("id_users", $id_users)->delete();
      Bill::where("id_users", $id_users)->delete();
      $total = 0;
      foreach ($tanggungan_pembayaran as $tb) {
        $bill = new Bill();
        $bill->id_bill = $tb->id_bill;
        $bill->id_orders = $tb->id_orders;
        $bill->id_users = $id_users;
        $bill->nominal = $tb->nominal;
        $bill->status = $tb->status;
        $bill->save();

        $total += $tb->nominal;
      }

      $tPem = new TanggunganPembayaran();
      $tPem->id_users = $id_users;
      $tPem->nominal = $total;
      $tPem->save();

      return response(["message" => "Data tanggungan berhasil disimpan"]);
    } catch (\Exception $e) {
      return response(["message" => $e->getMessage()]);
    }

  }
}

 ?>
