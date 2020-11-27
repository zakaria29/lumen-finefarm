<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class KembaliOrders extends Model
{
    protected $table = "kembali_orders";
    protected $primaryKey = "id_kembali_orders";
    protected $fillable = [
      "id_kembali_orders","waktu","id_orders","id_sopir"
    ];
    public $incrementing = false;
    public $timestamps = false;

    public function orders()
    {
      return $this->belongsTo("App\Orders","id_orders");
    }

    public function detail_kembali_orders()
    {
      return $this->hasMany("App\DetailKembaliOrders","id_kembali_orders","id_kembali_orders");
    }

    public function sopir()
    {
      return $this->belongsTo("App\Users","id_sopir","id_users");
    }

}
 ?>
