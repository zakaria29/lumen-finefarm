<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class SetorUang extends Model
{
    protected $table = "setor_uang";
    protected $primaryKey = "id_setor";
    protected $fillable = ["id_setor","waktu","id_users","nominal","id_pembeli","id_orders"];
    public $incrementing = false;
    public $timestamps = false;

    public function sopir()
    {
      return $this->belongsTo("App\Users","id_users");
    }

    public function pembeli()
    {
      return $this->belongsTo("App\Users","id_pembeli","id_users");
    }
}
 ?>
