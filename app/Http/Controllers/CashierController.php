<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Users;
class CashierController extends Controller
{
  public function get_all()
  {
    $list = array();
    $cashier = Users::where("id_level","3")->where("status","1");

    foreach ($cashier->get() as $o) {
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
      "cashier" => $list,
      "count" => $cashier->count()
    ]);
  }

  public function get($limit = 5, $offset = 0)
  {
    $list = array();
    $cashier = Users::where("id_level","3")->where("status","1")
    ->take($limit)->skip($offset)->get();
    $count = Users::where("id_level","3")->where("status","1")->count();

    foreach ($cashier as $o) {
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
      "cashier" => $list,
      "count" => $count
    ]);
  }

  public $find;
  public function find(Request $request, $limit = 5, $offset = 0)
  {
    $this->find = $request->find;
    $list = array();
    $cashier = Users::where(function($query){
      $query->where("id_level","3")->where("status","1");
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
    $count = $cashier->count();

    foreach ($cashier->take($limit)->skip($offset)->get() as $o) {
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
      "cashier" => $list,
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

      $cashier = new Users();
      $cashier->id_users= $id;
      $cashier->nama = $request->nama;
      $cashier->alamat = $request->alamat;
      $cashier->contact = $request->contact;
      $cashier->email = $request->email;
      $cashier->username = $request->username;
      $cashier->password = Crypt::encrypt($request->password);
      $cashier->status = $request->status;
      $cashier->id_level = $request->id_level;
      $file = $request->image;
      $fileName = time().".".$file->extension();
      $cashier->image = $fileName;
      $cashier->save();
      $request->file('image')->move(storage_path('image'), $fileName);
      return response([
        "message" => "Data kasir berhasil ditambahkan"
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
      $cashier = Users::where("id_users", $request->id_users)->first();
      if ($request->hasFile("image")) {
        $path = storage_path("image")."/".$cashier->image;
        if (file_exists($path)) {
          unlink($path);
        }
        $file = $request->image;
        $fileName = time().".".$file->extension();
        $cashier->image = $fileName;
        $request->file('image')->move(storage_path('image'), $fileName);
      }
      $cashier->nama = $request->nama;
      $cashier->alamat = $request->alamat;
      $cashier->contact = $request->contact;
      $cashier->email = $request->email;
      $cashier->username = $request->username;
      $cashier->password = Crypt::encrypt($request->password);
      $cashier->status = $request->status;
      $cashier->id_level = $request->id_level;
      $cashier->save();
      return response([
        "message" => "Data kasir berhasil diubah"
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
      $cashier = Users::where("id_users", $id)->first();
      $cashier->status = 0;
      $cashier->save();
      return response([
        "message" => "Data kasir berhasil dihapus"
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
    $owner = Users::where("id_level","3")->where("username", $username);
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
      $check = Users::where("id_level","3")->where("token",$token)->count();
      if ($check > 0) {
        return response([
          "auth" => true,
          "cashier" => Users::where("id_level","3")->where("token",$token)->first()
        ]);
      }else{
        return response(["auth" => false]);
      }
    } catch (\Exception $e) {
      return response(["auth" => false]);
    }
  }
}

 ?>
