<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class LogPack extends Model
{
    protected $table = "log_pack";
    protected $fillable = [
      "waktu","id_pack","jumlah","id_users","status","beli","id_pembeli","harga","stok"
    ];
    public $incrementing = false;
    public $timestamps = false;

    public function pack(){
      return $this->belongsTo("App\Pack","id_pack");
    }

    public function sopir(){
      return $this->belongsTo("App\Users","id_users");
    }
}
 ?>
