<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class DetailSupply extends Model
{
    protected $table = "detail_supply";
    protected $fillable = [
      "id_supply","id_barang","harga_beli","jumlah_utuh","jumlah_bentes",
      "jumlah_putih","jumlah_pecah","jumlah_loss"
    ];
    public $incrementing = false;
    public $timestamps = false;

    public function barang()
    {
      return $this->belongsTo("App\Barang","id_barang");
    }
}
 ?>
