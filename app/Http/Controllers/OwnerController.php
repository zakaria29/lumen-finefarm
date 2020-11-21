<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Users;
class OwnerController extends Controller
{
  public function get_all()
  {
    $list = array();
    $owner = Users::where("id_level","1")->where("status","1")->get();
    $count = Users::where("id_level","1")->where("status","1")->count();

    foreach ($owner as $o) {
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
      "owner" => $list,
      "count" => $count
    ]);
  }

  public function get($limit = 5, $offset = 0)
  {
    $list = array();
    $owner = Users::where("id_level","1")->where("status","1")
    ->take($limit)->skip($offset)->get();
    $count = Users::where("id_level","1")->where("status","1")->count();

    foreach ($owner as $o) {
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
      "owner" => $list,
      "count" => $count
    ]);
  }

  public $find;
  public function find(Request $request, $limit = 5, $offset = 0)
  {
    $this->find = $request->find;
    $list = array();
    $owner = Users::where(function($query){
      $query->where("id_level","1")->where("status","1");
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
    $count = $owner->count();

    foreach ($owner->take($limit)->skip($offset)->get() as $o) {
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
      "owner" => $list,
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

      $owner = new Users();
      $owner->id_users= $id;
      $owner->nama = $request->nama;
      $owner->alamat = $request->alamat;
      $owner->contact = $request->contact;
      $owner->email = $request->email;
      $owner->username = $request->username;
      $owner->password = Crypt::encrypt($request->password);
      $owner->status = $request->status;
      $owner->id_level = $request->id_level;
      $file = $request->image;
      $fileName = time().".".$file->extension();
      $owner->image = $fileName;
      $owner->save();
      $request->file('image')->move(storage_path('image'), $fileName);
      return response([
        "message" => "Data owner berhasil ditambahkan"
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
      $owner = Users::where("id_users", $request->id_users)->first();
      if ($request->hasFile("image")) {
        $path = storage_path("image")."/".$owner->image;
        if (file_exists($path)) {
          unlink($path);
        }
        $file = $request->image;
        $fileName = time().".".$file->extension();
        $owner->image = $fileName;
        $request->file('image')->move(storage_path('image'), $fileName);
      }
      $owner->nama = $request->nama;
      $owner->alamat = $request->alamat;
      $owner->contact = $request->contact;
      $owner->email = $request->email;
      $owner->username = $request->username;
      $owner->password = Crypt::encrypt($request->password);
      $owner->status = $request->status;
      $owner->id_level = $request->id_level;
      $owner->save();
      return response([
        "message" => "Data owner berhasil diubah"
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
      $owner = Users::where("id_users", $id)->first();
      $owner->status = 0;
      $owner->save();
      return response([
        "message" => "Data owner berhasil dihapus"
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
    $owner = Users::where("id_level","1")->where("username", $username);
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
      $check = Users::where("id_level","1")->where("token",$token)->count();
      if ($check > 0) {
        return response([
          "auth" => true,
          "owner" => Users::where("id_level","1")->where("token",$token)->first()
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
