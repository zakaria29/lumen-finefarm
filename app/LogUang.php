<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class LogUang extends Model
{
    protected $table = "log_uang";
    protected $primaryKey = "id_log_uang";
    protected $fillable = ["id_log_uang","waktu","nominal","status","event","id_users"];
    public $incrementing = false;
    public $timestamps = false;

    public function users()
    {
      return $this->belongsTo("App\Users","id_users");
    }
}
 ?>
