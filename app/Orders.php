<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    protected $table = "orders";
    protected $primaryKey = "id_orders";
    protected $fillable = [
      "id_orders","id_pembeli","id_users","waktu_order","waktu_pengiriman",
      "po","invoice","id_status_orders","tgl_jatuh_tempo","waktu",
      "tipe_pembayaran","total_bayar","id_sopir","down_payment","catatan"
    ];
    public $incrementing = false;
    public $timestamps = false;

    public function bill()
    {
      return $this->hasMany("App\Bill","id_users","id_pembeli")->where("status","1");
    }

    public function tanggungan_pack()
    {
      return $this->hasMany("App\TanggunganPack","id_users","id_pembeli")->with("pack");
    }

    public function pembeli()
    {
      return $this->belongsTo("App\Users","id_pembeli");
    }

    public function users()
    {
      return $this->belongsTo("App\Users","id_users");
    }

    public function sopir()
    {
      return $this->belongsTo("App\Users","id_sopir");
    }

    public function status_orders()
    {
      return $this->belongsTo("App\StatusOrders","id_status_orders");
    }

    public function detail_orders()
    {
      return $this->hasMany("App\DetailOrders","id_orders","id_orders");
    }

    public function rekap_detail_orders()
    {
      return $this->hasMany("App\RekapDetailOrders","id_orders","id_orders")
      ->orderBy("num_rekap","desc");
    }

    public function log_orders()
    {
      return $this->hasMany("App\LogOrders","id_orders","id_orders")
      ->orderBy("waktu","asc");
    }
}
 ?>
