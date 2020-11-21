<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class KembaliPack extends Model
{
    protected $table = "kembali_pack";
    protected $primaryKey = "id_kembali_pack";
    protected $fillable = [
      "id_kembali_pack","waktu","id_pack","jumlah","id_users","id_pembeli"
    ];
    public $incrementing = false;
    public $timestamps = false;

    public function pack()
    {
      return $this->belongsTo("App\Pack","id_pack");
    }

    public function sopir()
    {
      return $this->belongsTo("App\Users","id_users","id_users");
    }

    public function pembeli()
    {
      return $this->belongsTo("App\Users","id_pembeli","id_users");
    }
}
 ?>
