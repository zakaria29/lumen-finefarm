<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class LogStokBarang extends Model
{
    protected $table = "log_stok_barang";
    protected $fillable = [
      "waktu","event","id_users","id_supplier","id_barang","jumlah","id","status"
    ];
    public $incrementing = false;
    public $timestamps = false;

    public function users()
    {
      return $this->belongsTo("App\Users","id_users");
    }

    public function barang()
    {
      return $this->belongsTo("App\Barang","id_barang");
    }

    public function supplier()
    {
      return $this->belongsTo("App\Supplier","id_supplier");
    }
}
 ?>
