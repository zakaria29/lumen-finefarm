<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class Supply extends Model
{
    protected $table = "supply";
    protected $primaryKey = "id_supply";
    protected $fillable = ["id_supply","waktu","id_supplier","id_users","total_bayar"];
    public $incrementing = false;
    public $timestamps = false;

    public function supplier()
    {
      return $this->belongsTo("App\Supplier","id_supplier");
    }

    public function users()
    {
      return $this->belongsTo("App\Users","id_users");
    }

    public function detail_supply()
    {
      return $this->hasMany("App\DetailSupply","id_supply","id_supply");
    }
}
?>
