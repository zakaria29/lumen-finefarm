<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class RekapDetailOrders extends Model
{
    protected $table = "rekap_detail_orders";
    protected $fillable = [
      "waktu","id_orders","id_barang","id_pack",
      "jumlah_barang","jumlah_pack","harga_beli",
      "harga_pack","id_users","num_rekap"
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

    public function users()
    {
      return $this->belongsTo("App\Users","id_users");
    }
}
 ?>
