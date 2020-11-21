<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class TanggunganPack extends Model
{
    protected $table = "tanggungan_pack";
    protected $fillable = ["id_users","id_pack","jumlah"];
    public $incrementing = false;
    public $timestamps = false;

    public function pack()
    {
      return $this->belongsTo("App\Pack","id_pack");
    }

    public function users()
    {
      return $this->belongsTo("App\Users","id_users");
    }
}
 ?>
