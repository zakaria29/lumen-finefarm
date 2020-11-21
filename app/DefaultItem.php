<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class DefaultItem extends Model
{
    protected $table = "default_item";
    protected $fillable = ["id_group_customer","id_barang","id_pack"];
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
}
 ?>
