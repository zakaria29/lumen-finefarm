<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class DetailOrders extends Model
{
    protected $table = "detail_orders";
    protected $fillable = [
      "id_orders","id_barang","id_pack","jumlah_barang",
      "jumlah_pack","harga_beli","harga_pack","is_lock"
    ];
    public $incrementing = false;
    public $timestamps = false;

    public function barang()
    {
      return $this->belongsTo("App\Barang","id_barang");
    }

    public function pack()
    {
      return $this->belongsTo("App\Pack","id_pack");
    }
}
 ?>
