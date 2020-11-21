<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class TanggunganPembayaran extends Model
{
    protected $table = "tanggungan_pembayaran";
    protected $fillable = ["id_users","nominal"];
    public $incrementing = false;
    public $timestamps = false;

    public function users()
    {
      return $this->belongsTo("App\Users","id_users");
    }
}
 ?>
