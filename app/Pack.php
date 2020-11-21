<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class Pack extends Model
{
    protected $table = "pack";
    protected $primaryKey = "id_pack";
    protected $fillable = ["id_pack","nama_pack","stok","keterangan","harga"];
    public $incrementing = false;
    public $timestamps = false;

    public function barang(){
      return $this->hasMany("App\PackBarang","id_pack","id_pack");
    }

    public function log_pack(){
      return $this->hasMany("App\LogPack","id_pack","id_pack")
      ->orderBy("waktu","desc");
    }

    public function kembali_pack(){
      return $this->hasMany("App\KembaliPack","id_pack","id_pack")
      ->orderBy("waktu","desc");
    }
}
?>
