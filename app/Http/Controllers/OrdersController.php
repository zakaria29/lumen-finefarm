<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Orders;
use App\DetailOrders;
use App\RekapDetailOrders;
use App\LogOrders;
use App\Bill;
use App\LogGetSupplier;
use App\LogStokBarang;
use App\LogPack;
use App\StokBarang;
use App\TanggunganPembayaran;
use App\TanggunganPack;
use App\Pack;
use App\LogUang;
use App\Users;
use App\PackBarang;
use App\Barang;
use App\KembaliPack;
use App\SetorUang;

/**
 *
 */
class OrdersController extends Controller
{
  public function create_new_order(Request $request)
  {
    // insert orders
    $orders = new Orders();
    $orders->id_orders = "IDO".time().rand(1,1000);
    $orders->id_pembeli = $request->id_pembeli;
    $orders->id_users = $request->id_users;
    $orders->waktu_order = $request->waktu_order;
    $orders->waktu_pengiriman = $request->waktu_pengiriman;
    $orders->po = $request->po;
    $orders->invoice = $request->invoice;
    $orders->id_status_orders = ($request->id_users == null) ? "1" : "2";
    $orders->tgl_jatuh_tempo = $request->tgl_jatuh_tempo;
    $orders->catatan = $request->catatan;
    if ($request->tipe_pembayaran == "2") {
      // pakai DP
      $orders->tipe_pembayaran = "0";
      $orders->down_payment = $request->down_payment;
    } else {
      $orders->tipe_pembayaran = $request->tipe_pembayaran;
      $orders->down_payment = "0";
    }
    $orders->total_bayar = $request->total_bayar;
    $orders->id_sopir = $request->id_sopir;
    $orders->save();

    // insert log order
    $logOrder = new LogOrders();
    $logOrder->id_orders = $orders->id_orders;
    $logOrder->waktu = $orders->waktu_order;
    $logOrder->id_users = $orders->id_pembeli;
    $logOrder->id_status_orders = $orders->id_status_orders;
    $logOrder->save();

    if($request->tipe_pembayaran == "1"){
      // bayar tunai
      $logUang = new LogUang();
      $logUang->id_log_uang = $orders->id_orders;
      $logUang->waktu = $orders->waktu_order;
      $logUang->nominal = $orders->total_bayar;
      $logUang->status = "in";
      $logUang->event  = "Pembayaran Order";
      $logUang->id_users = $orders->id_pembeli;
      $logUang->save();
    } elseif ($request->tipe_pembayaran == "2") {
      // bayar DP
      $logUang = new LogUang();
      $logUang->id_log_uang = $orders->id_orders;
      $logUang->waktu = $orders->waktu_order;
      $logUang->nominal = $orders->down_payment;
      $logUang->status = "in";
      $logUang->event  = "Pembayaran Order";
      $logUang->id_users = $orders->id_pembeli;
      $logUang->save();
    }

    $detail_orders = json_decode($request->detail_orders);
    foreach ($detail_orders as $do) {
      $detail = new DetailOrders();
      $detail->id_orders = $orders->id_orders;
      $detail->id_barang = $do->id_barang;
      $detail->id_pack = $do->id_pack;
      $detail->jumlah_barang = $do->jumlah_barang;
      $detail->jumlah_pack = $do->jumlah_pack;
      $detail->harga_beli = $do->harga_beli;
      $detail->harga_pack = $do->harga_pack;
      $detail->save();

      $rekap = new RekapDetailOrders();
      $rekap->id_orders = $orders->id_orders;
      $rekap->id_barang = $do->id_barang;
      $rekap->id_pack = $do->id_pack;
      $rekap->jumlah_barang = $do->jumlah_barang;
      $rekap->jumlah_pack = $do->jumlah_pack;
      $rekap->harga_beli = $do->harga_beli;
      $rekap->harga_pack = $do->harga_pack;
      $rekap->id_users = $orders->id_pembeli;
      $rekap->num_rekap = "1";
      $rekap->save();
    }

    return response([
      "message" => "Data order berhasil ditambahkan"
    ]);
  }

  public function verify_order(Request $request, $limit = 5, $offset = 0)
  {
    $this->find = $request->find;
    $orders = Orders::where("id_status_orders", "1")->with([
      "pembeli","users", "sopir","status_orders","log_orders",
      "log_orders.users","log_orders.status_orders",
      "detail_orders","detail_orders.barang","detail_orders.pack"
    ])->where(function($q){
      $q->whereHas("detail_orders.barang", function($query){
        $query->where("nama_barang","like","%$this->find%");
      })
      ->orWhereHas("detail_orders.pack", function($query){
        $query->where("nama_pack","like","%$this->find%");
      })
      ->orWhereHas("pembeli", function($query){
        $query->where("nama","like","%$this->find%");
      })
      ->orWhereHas("sopir", function($query){
        $query->where("nama","like","%$this->find%");
      })
      ->orWhereHas("users", function($query){
        $query->where("nama","like","%$this->find%");
      })
      ->orWhere("po","like","%$this->find%")
      ->orWhere("invoice","like","%$this->find%");
    })->orderBy("waktu_order","desc");

      if ($limit == null or $offset == null) {
        return [
          "count" => $orders->count(),
          "orders" => $orders->get(),
        ];
      }else{
        return [
          "count" => $orders->count(),
          "orders" => $orders->take($limit)->skip($offset)->get(),
        ];
      }
  }

  public function prepare_order(Request $request, $limit = 5, $offset = 0)
  {
    $this->find = $request->find;
    $orders = Orders::where("id_status_orders", "2")
    ->where("waktu_pengiriman","<=",date("Y-m-d"))
    ->with([
      "pembeli","users", "sopir","status_orders","log_orders",
      "log_orders.users","log_orders.status_orders",
      "detail_orders","detail_orders.barang","detail_orders.pack"
    ])->where(function($q){
      $q->whereHas("detail_orders.barang", function($query){
        $query->where("nama_barang","like","%$this->find%");
      })
      ->orWhereHas("detail_orders.pack", function($query){
        $query->where("nama_pack","like","%$this->find%");
      })
      ->orWhereHas("pembeli", function($query){
        $query->where("nama","like","%$this->find%");
      })
      ->orWhereHas("sopir", function($query){
        $query->where("nama","like","%$this->find%");
      })
      ->orWhereHas("users", function($query){
        $query->where("nama","like","%$this->find%");
      })
      ->orWhere("po","like","%$this->find%")
      ->orWhere("invoice","like","%$this->find%");
    })->orderBy("waktu_order","desc");

      if ($limit == null or $offset == null) {
        return [
          "count" => $orders->count(),
          "orders" => $orders->get(),
        ];
      }else{
        return [
          "count" => $orders->count(),
          "orders" => $orders->take($limit)->skip($offset)->get(),
        ];
      }
  }

  public function update_order(Request $request)
  {
    // insert orders
    $orders = Orders::where("id_orders", $request->id_orders)->first();
    $orders->id_pembeli = $request->id_pembeli;
    $orders->id_users = $request->id_users;
    // $orders->waktu_order = date("Y-m-d H:i:s");
    $orders->waktu_pengiriman = $request->waktu_pengiriman;
    $orders->po = $request->po;
    $orders->invoice = $request->invoice;
    // $orders->id_status_orders = "1";
    $orders->tgl_jatuh_tempo = $request->tgl_jatuh_tempo;
    $orders->catatan = $request->catatan;
    if ($request->tipe_pembayaran == "2") {
      // pakai DP
      $orders->tipe_pembayaran = "0";
      $orders->down_payment = $request->down_payment;
    } else {
      $orders->tipe_pembayaran = $request->tipe_pembayaran;
      $orders->down_payment = "0";
    }
    $orders->total_bayar = $request->total_bayar;
    $orders->id_sopir = $request->id_sopir;
    $orders->save();

    // insert log order
    $logOrder = new LogOrders();
    $logOrder->id_orders = $orders->id_orders;
    $logOrder->waktu = date("Y-m-d H:i:s");
    $logOrder->id_users = $orders->id_pembeli;
    $logOrder->id_status_orders = $orders->id_status_orders;
    $logOrder->save();

    LogUang::where("id_log_uang", $orders->id_orders)->delete();

    if($request->tipe_pembayaran == "1"){
      // bayar tunai
      $logUang = new LogUang();
      $logUang->id_log_uang = $orders->id_orders;
      $logUang->waktu = $orders->waktu_order;
      $logUang->nominal = $orders->total_bayar;
      $logUang->status = "in";
      $logUang->event  = "Pembayaran Order";
      $logUang->id_users = $orders->id_pembeli;
      $logUang->save();
    } elseif ($request->tipe_pembayaran == "2") {
      // bayar DP
      $logUang = new LogUang();
      $logUang->id_log_uang = $orders->id_orders;
      $logUang->waktu = $orders->waktu_order;
      $logUang->nominal = $orders->down_payment;
      $logUang->status = "in";
      $logUang->event  = "Pembayaran Order";
      $logUang->id_users = $orders->id_pembeli;
      $logUang->save();
    }

    $num = RekapDetailOrders::where("id_orders",$request->id_orders)->max("num_rekap");
    DetailOrders::where("id_orders", $request->id_orders)->delete();
    $detail_orders = json_decode($request->detail_orders);
    foreach ($detail_orders as $do) {
      $detail = new DetailOrders();
      $detail->id_orders = $orders->id_orders;
      $detail->id_barang = $do->id_barang;
      $detail->id_pack = $do->id_pack;
      $detail->jumlah_barang = $do->jumlah_barang;
      $detail->jumlah_pack = $do->jumlah_pack;
      $detail->harga_beli = $do->harga_beli;
      $detail->harga_pack = $do->harga_pack;
      $detail->save();

      $rekap = new RekapDetailOrders();
      $rekap->id_orders = $orders->id_orders;
      $rekap->id_barang = $do->id_barang;
      $rekap->id_pack = $do->id_pack;
      $rekap->jumlah_barang = $do->jumlah_barang;
      $rekap->jumlah_pack = $do->jumlah_pack;
      $rekap->harga_beli = $do->harga_beli;
      $rekap->harga_pack = $do->harga_pack;
      $rekap->id_users = $orders->id_pembeli;
      $rekap->num_rekap = $num + 1;
      $rekap->save();
    }

    return response([
      "message" => "Data order berhasil diubah"
    ]);
  }

  public function accept_order(Request $request, $id)
  {
    $orders = Orders::where("id_orders", $id)->first();
    $orders->id_users = $request->id_users;
    $orders->id_sopir = $request->id_sopir;
    $orders->id_status_orders = "2";
    $orders->save();

    $logOrder = new LogOrders();
    $logOrder->id_orders = $id;
    $logOrder->waktu = date("Y-m-d H:i:s");
    $logOrder->id_users = $request->id_users;
    $logOrder->id_status_orders = $orders->id_status_orders;
    $logOrder->save();

    return response([
      "message" => "Data order telah disetujui"
    ]);
  }

  public function send_order(Request $request, $id_orders)
  {
    $orders = Orders::where("id_orders", $id_orders)->first();
    $orders->id_status_orders = "3";
    $orders->save();

    $logOrder = new LogOrders();
    $logOrder->id_orders = $id_orders;
    $logOrder->waktu = date("Y-m-d H:i:s");
    $logOrder->id_users = $request->id_users;
    $logOrder->id_status_orders = $orders->id_status_orders;
    $logOrder->save();

    $detail = json_decode($request->detail_ambil_stok);
    /*
    format detail:
    detail_ambil_stok:[
    {
      id_barang:IDB90..,
      supplier:[
        {
          id_supplier: IDS90...,
          jumlah: 90
        },
        {
          id_supplier: IDS60...,
          jumlah: 10
        }
      ]
    }
  ]
    */
    foreach ($detail as $d) {
      foreach ($d->supplier as $ds) {
        // table log_stok_barang
        $logStok = new LogStokBarang();
        $logStok->waktu = date("Y-m-d H:i:s");
        $logStok->event = "Ambil Stok Barang";
        $logStok->id_users = $request->id_users;
        $logStok->id_barang = $d->id_barang;
        $logStok->id_supplier = $ds->id_supplier;
        $logStok->jumlah = $ds->jumlah;
        $logStok->id = $id_orders;
        $logStok->status = "out";
        $logStok->save();

        // table log_get_supplier
        $logSupplier = new LogGetSupplier();
        $logSupplier->waktu = date("Y-m-d H:i:s");
        $logSupplier->id_orders = $id_orders;
        $logSupplier->id_supplier = $ds->id_supplier;
        $logSupplier->id_barang = $d->id_barang;
        $logSupplier->jumlah = $ds->jumlah;
        $logSupplier->save();

        // update stok_barang
        $stok = StokBarang::where("id_supplier", $ds->id_supplier)
        ->where("id_barang", $d->id_barang)->first();
        StokBarang::where("id_supplier", $ds->id_supplier)
        ->where("id_barang", $d->id_barang)
        ->update(["stok" => $stok->stok - $ds->jumlah]);
      }
    }

    if ($orders->tipe_pembayaran == "0") {
      // insert to bill dan tanggungan pembayaran
      $nominal = $orders->total_bayar - $orders->down_payment;

      $bill = new Bill();
      $bill->id_bill = "BILL".time().rand(1,1000);
      $bill->id_orders = $orders->id_orders;
      $bill->id_users = $orders->id_pembeli;
      $bill->nominal = $nominal;
      $bill->status = "1"; // aktif sebagai tagihan
      $bill->save();

      $tb = TanggunganPembayaran::where("id_users", $orders->id_pembeli);
      if ($tb->count() > 0) {
        // edit tambah tanggungan
        $tanggungan = $tb->first();
        TanggunganPembayaran::where("id_users", $orders->id_pembeli)
        ->update(["nominal" => $tanggungan->nominal + $nominal]);
      }else{
        // buat data tanggungan
        $tanggungan = new TanggunganPembayaran();
        $tanggungan->id_users = $orders->id_pembeli;
        $tanggungan->nominal = $nominal;
        $tanggungan->save();
      }
    }

    return response([
      "message" => "Order berhasil dikirim"
    ]);
  }

  public function ready_send_order(Request $request, $id, $limit, $offset)
  {
    $this->find = $request->find;
    $orders = Orders::where("id_status_orders", "3")
    ->where("id_sopir",$id)
    ->with([
      "pembeli","users", "sopir","status_orders","log_orders",
      "log_orders.users","log_orders.status_orders",
      "detail_orders","detail_orders.barang","detail_orders.pack"
    ])->where(function($q){
      $q->whereHas("detail_orders.barang", function($query){
        $query->where("nama_barang","like","%$this->find%");
      })
      ->orWhereHas("detail_orders.pack", function($query){
        $query->where("nama_pack","like","%$this->find%");
      })
      ->orWhereHas("pembeli", function($query){
        $query->where("nama","like","%$this->find%");
      })
      ->orWhereHas("sopir", function($query){
        $query->where("nama","like","%$this->find%");
      })
      ->orWhereHas("users", function($query){
        $query->where("nama","like","%$this->find%");
      })
      ->orWhere("po","like","%$this->find%")
      ->orWhere("invoice","like","%$this->find%");
    })->orderBy("waktu_order","desc");

      if ($limit == null or $offset == null) {
        return [
          "count" => $orders->count(),
          "orders" => $orders->get(),
        ];
      }else{
        return [
          "count" => $orders->count(),
          "orders" => $orders->take($limit)->skip($offset)->get(),
        ];
      }
  }

  public function coming_order(Request $request, $id, $limit, $offset)
  {
    $this->find = $request->find;
    $orders = Orders::where("id_status_orders", "4")
    ->where("id_sopir",$id)
    ->with([
      "pembeli","users", "sopir","status_orders","log_orders",
      "log_orders.users","log_orders.status_orders","bill","tanggungan_pack",
      "detail_orders","detail_orders.barang","detail_orders.pack",
    ])->where(function($q){
      $q->whereHas("detail_orders.barang", function($query){
        $query->where("nama_barang","like","%$this->find%");
      })
      ->orWhereHas("detail_orders.pack", function($query){
        $query->where("nama_pack","like","%$this->find%");
      })
      ->orWhereHas("pembeli", function($query){
        $query->where("nama","like","%$this->find%");
      })
      ->orWhereHas("sopir", function($query){
        $query->where("nama","like","%$this->find%");
      })
      ->orWhereHas("users", function($query){
        $query->where("nama","like","%$this->find%");
      })
      ->orWhere("po","like","%$this->find%")
      ->orWhere("invoice","like","%$this->find%");
    })->orderBy("waktu_order","desc");

      if ($limit == null or $offset == null) {
        return [
          "count" => $orders->count(),
          "orders" => $orders->get(),
        ];
      }else{
        return [
          "count" => $orders->count(),
          "orders" => $orders->take($limit)->skip($offset)->get(),
        ];
      }
  }

  public function deliver_order($id_orders, $id_users)
  {
    $orders = Orders::where("id_orders", $id_orders)->first();
    $orders->id_status_orders = "4";
    $orders->save();

    $logOrder = new LogOrders();
    $logOrder->id_orders = $id_orders;
    $logOrder->waktu = date("Y-m-d H:i:s");
    $logOrder->id_users = $id_users;
    $logOrder->id_status_orders = $orders->id_status_orders;
    $logOrder->save();

    foreach ($orders->detail_orders as $d) {
      $logPack = new LogPack();
      $logPack->waktu = date("Y-m-d H:i:s");
      $logPack->id_pack = $d->id_pack;
      $logPack->jumlah = $d->jumlah_pack;
      $logPack->id_users = $id_users;
      $logPack->id_pembeli = $orders->id_pembeli;
      $logPack->status = "out";
      $logPack->beli = ($d->harga_pack > 0) ? true : false;
      $logPack->harga = $d->harga_pack;
      $logPack->save();

      $pack = Pack::where("id_pack", $d->id_pack)->first();
      $pack->stok -= $d->jumlah_pack;
      $pack->save();
    }

    $orders = Orders::where("id_orders", $id_orders)->first();
    /* tanggungan pack */
    foreach ($orders->detail_orders as $do) {
      if($do->harga_pack == "0" && $do->pack->keterangan == "1"){
        // jika harga beli = 0 dan keterangan pack auto_pinjam
        $tanggunganPack = TanggunganPack::where("id_users", $orders->id_pembeli)
        ->where("id_pack", $do->id_pack);
        if ($tanggunganPack->count() > 0) {
          // edit
          $tp = $tanggunganPack->first();
          TanggunganPack::where("id_users", $orders->id_pembeli)
          ->where("id_pack", $do->id_pack)
          ->update(["jumlah" => $tp->jumlah + $do->jumlah_pack]);
        }else{
          // buat baru
          $tp = new TanggunganPack();
          $tp->id_users = $orders->id_pembeli;
          $tp->id_pack = $do->id_pack;
          $tp->jumlah = $do->jumlah_pack;
          $tp->save();
        }
      }
    }
    /* end tanggungan pack */

    return response([
      "message" => "Pengiriman order sedang berlangsung"
    ]);
  }

  public function delivered_order(Request $request, $id_orders)
  {
    $orders = Orders::where("id_orders", $id_orders)->first();
    $orders->id_status_orders = "5";
    $orders->save();

    $logOrder = new LogOrders();
    $logOrder->id_orders = $id_orders;
    $logOrder->waktu = date("Y-m-d H:i:s");
    $logOrder->id_users = $request->id_users;
    $logOrder->id_status_orders = $orders->id_status_orders;
    $logOrder->save();


    $setor_uang = json_decode($request->setor_uang);
    $kembali_pack = json_decode($request->kembali_pack);

    foreach ($setor_uang as $su) {
      // $su = id_orders
      $o = Orders::where("id_orders", $su)->first();
      $setorUang = new SetorUang();
      $setorUang->id_setor = "SU".time().rand(1,1000);
      $setorUang->waktu = date("Y-m-d H:i:s");
      $setorUang->id_users = $request->id_users;
      $setorUang->id_pembeli = $o->id_pembeli;
      $setorUang->nominal = $o->total_bayar-$o->down_payment;
      $setorUang->id_orders = $o->id_orders;
      $setorUang->save();

      $bill = Bill::where("id_orders", $su)->first();
      $bill->status = "0";
      $bill->save();
    }

    foreach ($kembali_pack as $kp) {
      $kembaliPack = new KembaliPack();
      $kembaliPack->id_kembali_pack = "KP".time().rand(1,1000);
      $kembaliPack->waktu = date("Y-m-d H:i:s");
      $kembaliPack->id_pack = $kp->id_pack;
      $kembaliPack->jumlah = $kp->jumlah;
      $kembaliPack->id_users = $request->id_users;
      $kembaliPack->id_pembeli = $request->id_pembeli;
      $kembaliPack->save();
    }

    return response([
      "message" => "Order telah diterima"
    ]);
  }

  public function verify_pack(Request $request)
  {
    /*
    struktur json
    [
      {
        "id_kembali_pack": "KP9090",
        "waktu" : "2020-09-09 10:10:19",
        "id_pack" : "IDP101",
        "jumlah" : "90",
        "id_users" : "IDU902",
        "id_pembeli" : "IDU9090",
        "status" : "1"
      }
    ]
    */
    $kembaliPack = json_decode($request->kembali_pack);
    foreach ($kembaliPack as $kp) {
      if ($kp->status == "1") {
        $logPack = new LogPack();
        $logPack->waktu = $kp->waktu;
        $logPack->id_pack = $kp->id_pack;
        $logPack->jumlah = $kp->jumlah_pack;
        $logPack->id_users = $kp->id_users;
        $logPack->id_pembeli = $kp->id_pembeli;
        $logPack->status = "in";
        $logPack->beli = false;
        $logPack->harga = 0;
        $logPack->save();

        $pack = Pack::where("id_pack", $d->id_pack)->first();
        $pack->stok += $d->jumlah_pack;
        $pack->save();

        /* update tanggungan pack */
        $tanggunganPack = TanggunganPack::where("id_users", $kp->id_pembeli)
        ->where("id_pack", $kp->id_pack);
        if ($tanggunganPack->count() > 0) {
          $tp = $tanggunganPack->first();
          TanggunganPack::where("id_users", $kp->id_pembeli)
          ->where("id_pack", $kp->id_pack)
          ->update(["jumlah" => $tp->jumlah - $kp->jumlah_pack]);
        }
      }

      /* hapus data kembali_pack */
      KembaliPack::where("id_kembali_pack", $kp->id_kembali_pack)->delete();
    }

    return response([
      "message" => "Pengembalian pack telah diverifikasi"
    ]);
  }

  public function verify_uang(Request $request)
  {
    /*
    struktur request json
    [
      {
        "id_setor" : "SET0923",
        "waktu" : "2020-09-09 09:09:10",
        "id_users" : "IDU9090",
        "id_pembeli" : "IDU9892",
        "nominal" : "10000",
        "id_orders" : "IDO989",
        "status" : "0"
      }
    ]
    */
    $setorUang = json_decode($request->setor_uang);
    foreach ($setorUang as $su) {
      if ($su->status == "1") {
        $logUang = LogUang::where("id_log_uang", $su->id_orders);
        if ($logUang->count() > 0) {
          $lu = $logUang->first();
          $lu->nominal += $su->nominal;
          $lu->save();
        }else{
          $lu = new LogUang();
          $lu->id_log_uang = $su->id_orders;
          $lu->waktu = $su->waktu;
          $lu->nominal = $su->nominal;
          $lu->event = "Pelunasan Orders";
          $lu->id_users = $su->id_pembeli;
          $lu->status = "in";
          $lu->save();
        }

        /* tanggungan pembayaran */
        $tp = TanggunganPembayaran::where("id_users", $su->id_pembeli)->first();
        TanggunganPembayaran::where("id_users", $su->id_pembeli)
        ->update(["nominal" => $tp->nominal - $su->nominal]);
      }else{
        $bill = Bill::where("id_orders", $su->id_orders)->first();
        $bill->status = "1";
        $bill->save();
      }

      /* hapus data setor */
      SetorUang::where("id_setor", $su->id_setor)->delete();
    }

    return response([
      "message" => "Setoran uang telah diverifikasi"
    ]);
  }

  public function pay_orders(Request $request)
  {
    $file = $request->bukti;
    $fileName = time().".".$file->extension();
    $request->file('bukti')->move(storage_path('bukti'), $fileName);

    $orders = json_decode($request->pembayaran_orders);
    foreach ($orders as $o) {
      $pay = new PembayaranOrders();
      $pay->id_pay = "PAY".time().rand(1,1000);
      $pay->nominal = $o->nominal;
      $pay->id_orders = $o->id_orders;
      $pay->bukti = $fileName;
      $pay->keterangan = null;
      $pay->waktu = date("Y-m-d H:i:s");
      $pay->id_users = $request->id_users;
      $pay->save();

      $bill = Bill::where("id_orders",$o->id_orders)->first();
      $bill->status = "0";
      $bill->save();
    }

    return response([
      "message" => "Pembayaran order berhasil"
    ]);
  }

  public function verify_pembayaran(Request $request)
  {
    $pay = PembayaranOrders::where("id_pay", $request->id_pay)->first();
    $pay->keterangan = $request->keterangan;
    $pay->save();

    if ($pay->keterangan == "1") {
      Bill::where("id_orders", $pay->$id_orders)->delete();
      $tp = TanggunganPembayaran::where("id_users", $pay->id_users)->first();
      TanggunganPembayaran::where("id_users", $pay->id_users)
      ->update(["nominal" => $tp->nominal - $pay->nominal]);

      $logUang = LogUang::where("id_log_uang", $pay->id_orders);
      if ($logUang->count() > 0) {
        $lu = $logUang->first();
        $lu->nominal += $su->nominal;
        $lu->save();
      }else{
        $lu = new LogUang();
        $lu->id_log_uang = $pay->id_orders;
        $lu->waktu = $pay->waktu;
        $lu->nominal = $pay->nominal;
        $lu->event = "Pelunasan Orders";
        $lu->id_users = $pay->id_users;
        $lu->status = "in";
        $lu->save();
      }
    }else{
      $bill = Bill::where("id_orders", $pay->id_orders)->first();
      $bill->status = "1";
      $bill->save();
    }

    return response([
      "message" => "Pembayaran telah diverifikasi"
    ]);
  }

  public $find = "";
  public function searching(Request $request, $limit = 5, $offset = 0)
  {
    $this->find = $request->find;
    $from = $request->from." 00:00:00";
    $to = $request->to." 23:59:59";
    $orders = Orders::whereBetween("waktu_order", [$from, $to])->with([
      "pembeli","users", "sopir","status_orders","log_orders",
      "log_orders.users","log_orders.status_orders",
      "detail_orders","detail_orders.barang","detail_orders.pack"
    ])->where(function($q){
      $q->whereHas("detail_orders.barang", function($query){
        $query->where("nama_barang","like","%$this->find%");
      })
      ->orWhereHas("detail_orders.pack", function($query){
        $query->where("nama_pack","like","%$this->find%");
      })
      ->orWhereHas("pembeli", function($query){
        $query->where("nama","like","%$this->find%");
      })
      ->orWhereHas("sopir", function($query){
        $query->where("nama","like","%$this->find%");
      })
      ->orWhereHas("users", function($query){
        $query->where("nama","like","%$this->find%");
      })
      ->orWhere("po","like","%$this->find%")
      ->orWhere("invoice","like","%$this->find%");
    })->orderBy("waktu_order","desc");

      if ($limit == null or $offset == null) {
        return [
          "count" => $orders->count(),
          "orders" => $orders->get(),
        ];
      }else{
        return [
          "count" => $orders->count(),
          "orders" => $orders->take($limit)->skip($offset)->get(),
        ];
      }
  }

  public function get($id)
  {
    $o = Orders::where("id_orders",$id)->first();
    $itemDetail = array();
    foreach ($o->detail_orders as $d) {
      $package = array();
      $b = Barang::where("id_barang",$d->id_barang)->first();
      foreach ($b->pack as $p) {
        $itemPack = [
          "id_pack" => $p->id_pack,
          "nama_pack" => $p->pack->nama_pack,
          "keterangan" => $p->pack->keterangan,
          "harga" => $p->pack->harga,
          // "kapasitas" => $p->kapasitas * 10
        ];
        array_push($package, $itemPack);
      }
      $item = [
        "pack" => $package,
        "id_barang" => $d->id_barang,
        // "nama_barang" => $d->barang->nama_barang,
        "jumlah_barang" => $d->jumlah_barang,
        "harga_beli" => $d->harga_beli,
        "id_pack" => $d->id_pack,
        // "nama_pack" => $d->pack->nama_pack,
        "jumlah_pack" => $d->jumlah_pack,
        "harga_pack" => $d->harga_pack,
        "beli_pack" => ($d->harga_pack > 0) ? "1" : ""
      ];
      array_push($itemDetail, $item);
    }

    $itemLog = array();
    foreach ($o->log_orders as $l) {
      $item = [
        "waktu" => $l->waktu,
        "id_users" => $l->id_users,
        "nama" => $l->users->nama,
        "id_status_orders" => $l->id_status_orders,
        "nama_status_order" => $l->status_orders->nama_status_order
      ];
      array_push($itemLog, $item);
    }

    $customer = Users::where("id_users", $o->id_pembeli)->first();
    $items = [
      "id_orders" => $o->id_orders,
      "id_users" => $o->id_users,
      "nama_kasir" => $o->users->nama,
      "id_pembeli" => $o->id_pembeli,
      "nama_pembeli" => ($o->id_pembeli == null) ? "" : $o->pembeli->nama,
      "id_sopir" => $o->id_sopir,
      "nama_sopir" => ($o->id_sopir == null) ? "" : $o->sopir->nama,
      "waktu_order" => $o->waktu_order,
      "waktu_pengiriman" => $o->waktu_pengiriman,
      "po" => $o->po,
      "invoice" => $o->invoice,
      "tgl_jatuh_tempo" => $o->tgl_jatuh_tempo,
      "id_status_orders" => $o->id_status_orders,
      "nama_status_order" => $o->status_orders->nama_status_order,
      "tipe_pembayaran" => $o->tipe_pembayaran,
      "total_bayar" => $o->total_bayar,
      "catatan" => $o->catatan,
      "down_payment" => $o->down_payment,
      "detail_orders" => $itemDetail,
      "log_orders" => $itemLog,
      "margin" => $customer->margin,
      "margin_group" => $customer->group_customer->margin
    ];

    return response([
      "orders" => $items
    ]);
  }
}

 ?>
