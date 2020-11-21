<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class LogGetSupplier extends Model
{
    protected $table = "log_get_supplier";
    protected $fillable = ["waktu","id_orders","id_supplier","id_barang","jumlah"];
    public $incrementing = false;
    public $timestamps = false;

    public function orders()
    {
      return $this->belongsTo("App\Orders","id_orders");
    }

    public function supplier()
    {
      return $this->belongsTo("App\Supplier","id_supplier");
    }
}
 ?>
