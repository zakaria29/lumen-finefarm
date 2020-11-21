<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class PackBarang extends Model
{
    protected $table = "pack_barang";
    protected $fillable = ["id_barang","id_pack","kapasitas_kg","kapasitas_butir"];
    public $incrementing = false;
    public $timestamps = false;

    public function barang(){
      return $this->belongsTo("App\Barang","id_barang");
    }

    public function pack(){
      return $this->belongsTo("App\Pack","id_pack");
    }
}
?>
