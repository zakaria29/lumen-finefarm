<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class Users extends Model
{
    protected $table = "users";
    protected $primaryKey = "id_users";
    protected $fillable = [
      "id_users","nama","alamat","contact","email","image","id_level",
      "status","username","password","margin","nama_instansi",
      "bidang_usaha","id_group_customer","token","jatuh_tempo","inisial"
    ];
    public $incrementing = false;
    public $timestamps = false;

    public function level()
    {
      return $this->belongsTo("App\Level","id_level");
    }

    public function group_customer()
    {
      return $this->belongsTo("App\GroupCustomer","id_group_customer");
    }

    public function orders()
    {
      return $this->hasMany("App\Orders","id_pembeli","id_users");
    }

    public function pengiriman_order()
    {
      return $this->hasMany("App\Orders","id_sopir","id_users");
    }

    public function bill()
    {
      return $this->hasMany("App\Bill","id_users","id_users");
    }

    public function unverified_bill()
    {
      return $this->hasMany("App\Bill","id_users","id_users")->where("status","0");
    }

    public function tanggungan_pack()
    {
      return $this->hasMany("App\TanggunganPack","id_users","id_users");
    }

    public function tanggungan_pembayaran()
    {
      return $this->hasOne("App\TanggunganPembayaran","id_users","id_users");
    }

    public function log_pack()
    {
      return $this->hasMany("App\LogPack","id_pembeli","id_users");
    }

    public function kembali_pack()
    {
      return $this->hasMany("App\KembaliPack","id_pembeli","id_users");
    }

    public function setor_uang()
    {
      return $this->hasMany("App\SetorUang","id_pembeli","id_users");
    }

    public function membawa_pack()
    {
      return $this->hasMany("App\KembaliPack","id_users","id_users");
    }

    public function membawa_uang()
    {
      return $this->hasMany("App\SetorUang","id_users","id_users");
    }

    public function lock_pack_barang()
    {
      return $this->hasMany("App\LockPackBarang","id_users","id_users");
    }
}
 ?>
