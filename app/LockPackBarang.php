<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class LockPackBarang extends Model
{
    protected $table = "lock_pack_barang";
    protected $fillable = [
      "id_users","id_barang","id_pack","harga","kapasitas_kg","kapasitas_butir"
    ];
    public $incrementing = false;
    public $timestamps = false;

    public function barang(){
      return $this->belongsTo("App\Barang","id_barang");
    }

    public function pack(){
      return $this->belongsTo("App\Pack","id_pack");
    }

    public function users()
    {
      return $this->belongsTo("App\Users","id_users");
    }
}
 ?>
