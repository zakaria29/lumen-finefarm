<?php
namespace App;
use DB;
use Illuminate\Database\Eloquent\Model;

class KapasitasPack extends Model
{
    protected $table = "kapasitas_pack";
    protected $fillable = ["id_pack","kapasitas","jumlah","satuan"];
    public $incrementing = false;
    public $timestamps = false;

    public function pack()
    {
      return $this->belongsTo("App\Pack","id_pack");
    }
}
?>
