<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class ReturOrder extends Model
{
    protected $table = "retur_order";
    protected $primaryKey = "id_retur_order";
    protected $fillable = [
      "id_retur_order","id_pembeli","id_users","waktu_order","waktu_pengiriman",
      "po","invoice","id_status_orders","tgl_jatuh_tempo","waktu",
      "tipe_pembayaran","total_bayar","id_sopir","down_payment","catatan","kendala","status"
    ];
    public $incrementing = false;
    public $timestamps = false;


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

    public function detail_retur_order()
    {
      return $this->hasMany("App\DetailReturOrder","id_retur_order","id_retur_order");
    }

}
 ?>
