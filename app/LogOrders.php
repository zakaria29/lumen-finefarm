<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class LogOrders extends Model
{
    protected $table = "log_orders";
    protected $fillable = ["waktu","id_orders","id_users","id_status_orders"];
    public $incrementing = false;
    public $timestamps = false;

    public function status_orders()
    {
      return $this->belongsTo("App\StatusOrders","id_status_orders");
    }

    public function users()
    {
      return $this->belongsTo("App\Users","id_users");
    }
}
 ?>
