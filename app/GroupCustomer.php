<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class GroupCustomer extends Model
{
    protected $table = "group_customer";
    protected $primaryKey = "id_group_customer";
    protected $fillable = ["id_group_customer","nama_group_customer","margin"];
    public $incrementing = true;
    public $timestamps = false;

    public function default_item()
    {
      return $this->hasMany("App\DefaultItem","id_group_customer","id_group_customer");
    }

    public function customer()
    {
      return $this->hasMany("App\Users","id_group_customer","id_group_customer");
    }
}
 ?>
