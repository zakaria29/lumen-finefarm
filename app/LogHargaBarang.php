<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class LogHargaBarang extends Model
{
    protected $table = "log_harga_barang";
    protected $fillable = ["waktu","id_barang","harga","id_users"];
    public $incrementing = false;
    public $timestamps = false;

    public function barang()
    {
      return $this->belongsTo("App\Barang","id_barang");
    }

    public function users()
    {
      return $this->belongsTo("App\Users","id_users");
    }
}
 ?>
