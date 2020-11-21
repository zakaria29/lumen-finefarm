<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    protected $table = "level";
    protected $primaryKey = "id_level";
    protected $fillable = ["id_level","nama_level"];
    public $incrementing = false;
    public $timestamps = false;

    public function users()
    {
      return $this->hasMany("App\Users","id_level","id_level");
    }
}
 ?>
