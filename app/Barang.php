<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    protected $table = "barang";
    protected $primaryKey = "id_barang";
    protected $fillable = ["id_barang","nama_barang","keterangan","satuan"];
    public $incrementing = false;
    public $timestamps = false;

    public function stok(){
      return $this->hasMany("App\StokBarang","id_barang","id_barang");
    }

    public function pack(){
      return $this->hasMany("App\PackBarang","id_barang","id_barang");
    }

    public function log_harga(){
      return $this->hasMany("App\LogHargaBarang","id_barang","id_barang")
      ->orderBy("waktu","desc")->take(20);
    }

    public function log_stok_barang(){
      return $this->hasMany("App\LogStokBarang","id_barang","id_barang");
    }

    public function harga_barang()
    {
      return $this->hasMany("App\LogHargaBarang","id_barang","id_barang")
      ->orderBy("waktu","desc");

    }
}
