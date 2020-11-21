<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class StatusOrders extends Model
{
    protected $table = "status_orders";
    protected $primaryKey = "id_status_orders";
    protected $fillable = ["id_status_orders","nama_status_order"];
    public $incrementing = false;
    public $timestamps = false;
}
 ?>
