<?php
namespace App;
use DB;
use Illuminate\Database\Eloquent\Model;

class Pack extends Model
{
    protected $table = "pack";
    protected $primaryKey = "id_pack";
    protected $fillable = ["id_pack","nama_pack","stok","keterangan","harga"];
    public $incrementing = false;
    public $timestamps = false;

    public function kapasitas_kg()
    {
      return $this->hasMany("App\KapasitasPack","id_pack","id_pack")->where("satuan","1");
    }

    public function kapasitas_butir()
    {
      return $this->hasMany("App\KapasitasPack","id_pack","id_pack")->where("satuan","2");
    }

    public function barang(){
      return $this->hasMany("App\PackBarang","id_pack","id_pack");
    }

    public function log_pack(){
      return $this->hasMany("App\LogPack","id_pack","id_pack");
    }

    public function kembali_pack(){
      return $this->hasMany("App\KembaliPack","id_pack","id_pack")
      ->orderBy("waktu","desc");
    }

    public function detail_orders()
    {
      return $this->hasMany("App\DetailOrders","id_pack","id_pack");
    }

    public function tanggungan_pack()
    {
      return $this->hasMany("App\TanggunganPack","id_pack","id_pack");
    }

}
?>
