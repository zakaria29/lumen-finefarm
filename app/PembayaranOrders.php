<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class PembayaranOrders extends Model
{
    protected $table = "pembayaran_orders";
    protected $primaryKey = "id_pay";
    protected $fillable = [
      "id_pay","id_orders","nominal","bukti","keterangan","waktu","id_users"
    ];
    public $incrementing = false;
    public $timestamps = false;

    public function users()
    {
      return $this->belongsTo("App\Users","id_users");
    }

    public function orders()
    {
      return $this->belongsTo("App\Orders","id_orders");
    }
}
 ?>
