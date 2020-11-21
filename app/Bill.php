<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    protected $table = "bill";
    protected $primaryKey = "id_bill";
    protected $fillable = ["id_bill","id_orders","id_users","nominal","status"];
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
