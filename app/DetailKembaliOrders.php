<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class DetailKembaliOrders extends Model
{
    protected $table = "detail_kembali_orders";
    protected $fillable = [
      "id_kembali_orders","id_barang","jumlah_barang"
    ];
    public $incrementing = false;
    public $timestamps = false;

    public function barang()
    {
      return $this->belongsTo("App\Barang","id_barang");
    }
}
 ?>
