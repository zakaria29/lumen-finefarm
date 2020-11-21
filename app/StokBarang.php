<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class StokBarang extends Model
{
    protected $table = "stok_barang";
    protected $fillable = ["id_barang","id_supplier","stok"];
    public $incrementing = false;
    public $timestamps = false;

    public function supplier()
    {
      return $this->belongsTo("App\Supplier","id_supplier");
    }

    public function barang()
    {
      return $this->belongsTo("App\Barang","id_barang");
    }
}
 ?>
