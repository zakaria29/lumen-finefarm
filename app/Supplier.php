<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $table = "supplier";
    protected $primaryKey = "id_supplier";
    protected $fillable = ["id_supplier","nama_supplier","alamat","kontak"];
    public $incrementing = false;
    public $timestamps = false;

    public function stok_barang(){
      return $this->hasMany("App\StokBarang","id_supplier","id_supplier");
    }

    public function log_supply(){
      return $this->hasMany("App\LogStokBarang","id_supplier","id_supplier")
      ->orderBy("waktu","desc");
    }
}
 ?>
