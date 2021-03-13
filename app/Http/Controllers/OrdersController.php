<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;
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
use App\PembayaranOrders;
use DB;
use App\FPDF\FPDF;
use App\KembaliOrders;
use App\DetailKembaliOrders;
use App\ReturOrder;
use App\DetailReturOrder;
use App\Exports\Order;
use PDF;

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
    $orders->id_status_orders = ($request->id_users == '') ? "1" : "2";
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
    $orders->id_sopir = ($request->id_sopir == '') ? null : $request->id_sopir;
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
      $detail->is_lock = $do->is_lock;
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
      "pembeli","users", "sopir","status_orders","log_orders","pembeli.group_customer",
      "log_orders.users","log_orders.status_orders","detail_orders.barang.current_harga",
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
    if ($request->verify == true) {
      $orders->id_status_orders = "2";
    }
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
    if ($request->verify == true) {
      $logOrder = new LogOrders();
      $logOrder->id_orders = $orders->id_orders;
      $logOrder->waktu = date("Y-m-d H:i:s");
      $logOrder->id_users = $orders->id_pembeli;
      $logOrder->id_status_orders = $orders->id_status_orders;
      $logOrder->save();
    }


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
      $detail->is_lock = $do->is_lock;
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

    $num = RekapDetailOrders::where("id_orders",$id)->max("num_rekap");
    DetailOrders::where("id_orders", $id)->delete();
    $detail_orders = json_decode($request->detail_orders);
    $total = 0;
    foreach ($detail_orders as $do) {
      $detail = new DetailOrders();
      $detail->id_orders = $id;
      $detail->id_barang = $do->id_barang;
      $detail->id_pack = $do->id_pack;
      $detail->jumlah_barang = $do->jumlah_barang;
      $detail->jumlah_pack = $do->jumlah_pack;
      $detail->harga_beli = $do->harga_beli;
      $detail->harga_pack = $do->harga_pack;
      $detail->is_lock = $do->is_lock;
      $detail->save();

      $total += ($do->jumlah_barang * $do->harga_beli) + ($do->jumlah_pack * $do->harga_pack);

      $rekap = new RekapDetailOrders();
      $rekap->id_orders = $id;
      $rekap->id_barang = $do->id_barang;
      $rekap->id_pack = $do->id_pack;
      $rekap->jumlah_barang = $do->jumlah_barang;
      $rekap->jumlah_pack = $do->jumlah_pack;
      $rekap->harga_beli = $do->harga_beli;
      $rekap->harga_pack = $do->harga_pack;
      $rekap->id_users = $request->id_users;
      $rekap->num_rekap = $num + 1;
      $rekap->save();
    }

    $logOrder = new LogOrders();
    $logOrder->id_orders = $id;
    $logOrder->waktu = date("Y-m-d H:i:s");
    $logOrder->id_users = $request->id_users;
    $logOrder->id_status_orders = $orders->id_status_orders;
    $logOrder->save();

    Orders::where("id_orders", $id)->update(["total_bayar" => $total]);

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
        $brg = StokBarang::where("id_barang",$d->id_barang)
        ->where("id_supplier", $ds->id_supplier)->first();
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
        $logStok->stok = $brg->stok - $ds->jumlah;
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

  public function kirim_order(Request $request)
  {
    /***
    format json:

    kirim_order = [
      {
        id_orders: "IDO999",
        ambil_stok: [
          { id_supplier:"IDS2323", id_barang:"IDB111", jumlah:100 },
          { id_supplier:"IDS2222", id_barang:"IDB222", jumlah:50 }
        ]
      },
      {
        id_orders: "IDO101",
        detail_orders: [ { id_supplier:"IDS2323", id_barang:"IDB111", jumlah:100 } ]
      }
    ]

    id_users: "IDU222201"
    */
    $kirim_order = json_decode($request->kirim_order);
    foreach ($kirim_order as $kirim) {
      /** update status dan insert log */
      $orders = Orders::where("id_orders", $kirim->id_orders)->first();
      $orders->id_status_orders = "3";
      $orders->save();

      $logOrder = new LogOrders();
      $logOrder->id_orders = $kirim->id_orders;
      $logOrder->waktu = date("Y-m-d H:i:s");
      $logOrder->id_users = $request->id_users;
      $logOrder->id_status_orders = $orders->id_status_orders;
      $logOrder->save();


      foreach ($kirim->get_stok as $as) {
        $brg = StokBarang::where("id_barang",$as->id_barang)
        ->where("id_supplier", $as->id_supplier)->first();
        // table log_stok_barang
        $logStok = new LogStokBarang();
        $logStok->waktu = date("Y-m-d H:i:s");
        $logStok->event = "Ambil Stok Barang";
        $logStok->id_users = $request->id_users;
        $logStok->id_barang = $as->id_barang;
        $logStok->id_supplier = $as->id_supplier;
        $logStok->jumlah = $as->jumlah;
        $logStok->id = $kirim->id_orders;
        $logStok->status = "out";
        $logStok->stok = $brg->stok - $as->jumlah;
        $logStok->save();

        // table log_get_supplier
        $logSupplier = new LogGetSupplier();
        $logSupplier->waktu = date("Y-m-d H:i:s");
        $logSupplier->id_orders = $kirim->id_orders;
        $logSupplier->id_supplier = $as->id_supplier;
        $logSupplier->id_barang = $as->id_barang;
        $logSupplier->jumlah = $as->jumlah;
        $logSupplier->save();

        // update stok_barang
        $stok = StokBarang::where("id_supplier", $as->id_supplier)
        ->where("id_barang", $as->id_barang)->first();
        StokBarang::where("id_supplier", $as->id_supplier)
        ->where("id_barang", $as->id_barang)
        ->update(["stok" => $stok->stok - $as->jumlah]);
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
      $pack = Pack::where("id_pack", $d->id_pack)->first();
      $pack->stok -= $d->jumlah_pack;
      $pack->save();

      $logPack = new LogPack();
      $logPack->waktu = date("Y-m-d H:i:s");
      $logPack->id_pack = $d->id_pack;
      $logPack->jumlah = $d->jumlah_pack;
      $logPack->id_users = $id_users;
      $logPack->id_pembeli = $orders->id_pembeli;
      $logPack->status = "out";
      $logPack->beli = ($d->harga_pack > 0) ? true : false;
      $logPack->harga = $d->harga_pack;
      $logPack->stok = $pack->stok;
      $logPack->save();
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

    /** pengembalian order */

    $detail_kembali_orders = json_decode($request->detail_kembali_orders);
    if (count($detail_kembali_orders) > 0) {
      $kembali = new KembaliOrders();
      $kembali->id_kembali_orders = "IKO".time().rand(1,1000);
      $kembali->id_orders = $id_orders;
      $kembali->id_sopir = $request->id_users;
      $kembali->waktu = date("Y-m-d H:i:s");
      $kembali->status = false;
      $kembali->save();

      foreach ($detail_kembali_orders as $dk) {
        $detail = new DetailKembaliOrders();
        $detail->id_kembali_orders = $kembali->id_kembali_orders;
        $detail->id_barang = $dk->id_barang;
        $detail->jumlah_barang = $dk->jumlah_barang;
        $detail->id_pack = $dk->id_pack;
        $detail->jumlah_pack = $dk->jumlah_pack;
        $detail->save();
      }
    }

    $id_retur_order = $id_orders;
    $detail_retur_order = json_decode($request->detail_retur_order);
    if (count($detail_retur_order) > 0) {
      $total = 0;
      foreach ($detail_retur_order as $dro) {
        DetailReturOrder::create([
          "id_retur_order" => $id_retur_order,
          "id_barang" => $dro->id_barang,
          "id_pack" => $dro->id_pack,
          "jumlah_barang" => $dro->jumlah_barang,
          "jumlah_pack" => $dro->jumlah_pack,
          "harga_beli" => $dro->harga_beli,
          "harga_pack" => $dro->harga_pack,
          "is_lock" => $dro->is_lock
        ]);
        $total += (($dro->jumlah_barang * $dro->harga_beli) + ($dro->harga_pack * $dro->jumlah_pack));
      }

      ReturOrder::create([
        "id_retur_order" => $id_retur_order,
        "id_users" => $orders->id_users,
        "id_pembeli" => $orders->id_pembeli,
        "waktu_order" => $orders->waktu_order,
        "waktu_pengiriman" => $orders->waktu_pengiriman,
        "po" => $orders->po,
        "invoice" => $orders->invoice,
        "id_status_orders" => $orders->id_status_orders,
        "tgl_jatuh_tempo" => $orders->tgl_jatuh_tempo,
        "tipe_pembayaran" => $orders->tipe_pembayaran,
        "total_bayar" => $total,
        "id_sopir" => $orders->id_sopir,
        "down_payment" => $orders->down_payment,
        "catatan" => $request->keterangan_retur,
        "kendala" => $orders->kendala,
        "status" => false
      ]);
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
        $pack = Pack::where("id_pack", $kp->id_pack)->first();
        $pack->stok += $kp->jumlah;
        $pack->save();

        $logPack = new LogPack();
        $logPack->waktu = $kp->waktu;
        $logPack->id_pack = $kp->id_pack;
        $logPack->jumlah = $kp->jumlah;
        $logPack->id_users = $kp->id_users;
        $logPack->id_pembeli = $kp->id_pembeli;
        $logPack->status = "in";
        $logPack->beli = false;
        $logPack->harga = 0;
        $logPack->stok = $pack->stok;
        $logPack->save();

        /* update tanggungan pack */
        $tanggunganPack = TanggunganPack::where("id_users", $kp->id_pembeli)
        ->where("id_pack", $kp->id_pack);
        if ($tanggunganPack->count() > 0) {
          $tp = $tanggunganPack->first();
          TanggunganPack::where("id_users", $kp->id_pembeli)
          ->where("id_pack", $kp->id_pack)
          ->update(["jumlah" => $tp->jumlah - $kp->jumlah]);
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

        $bill = Bill::where("id_orders", $su->id_orders)->first();
        $bill->status = "2";
        $bill->save();
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
    try {
      // $file = $request->bukti;
      // $fileName = time().".".$file->extension();
      // $request->file('bukti')->move(storage_path('bukti'), $fileName);

      $folderId = "1ioI9YSgYU_tlgW2JTG_GPqqPN15RiAV2";
      $file = $request->bukti;
      $filename = $file->getClientOriginalName();
      config(['filesystems.disks.google.folderId' => $folderId]);
      Storage::disk('google')->put($filename, file_get_contents($file));
      $link = Storage::disk('google')->url($filename);
      $url = explode("/", $link);
      $idFile = end($url);

      $orders = json_decode($request->pembayaran_orders);
      foreach ($orders as $o) {
        $pay = new PembayaranOrders();
        $pay->id_pay = "PAY".time().rand(1,1000);
        $pay->nominal = $o->nominal;
        $pay->id_orders = $o->id_orders;
        $pay->bukti = $link;
        $pay->keterangan = "0";
        $pay->waktu = date("Y-m-d H:i:s");
        $pay->id_users = $request->id_users;
        $pay->save();

        $bill = Bill::where("id_orders",$o->id_orders)->first();
        $bill->status = "0";
        $bill->save();
      }

      return response([
        "message" => "Pembayaran order berhasil, mohon tunggu verifikasi dari kasir"
      ]);
    } catch (\Exception $e) {
      return response(["error" => $e->getMessage()]);
    }
  }

  public function get_verify_pembayaran()
  {
    return response(PembayaranOrders::where("keterangan","0")
    ->with([
      "users","orders","orders.detail_orders","orders.detail_orders.barang",
      "orders.detail_orders.pack","orders.pembeli","orders.sopir"
    ])
    ->orderBy("waktu","asc")->get());
  }

  public function verify_pembayaran(Request $request)
  {
    $pay = PembayaranOrders::where("id_pay", $request->id_pay)->first();
    $pay->keterangan = $request->keterangan;
    $pay->save();

    if ($pay->keterangan == "2") {
      // Bill::where("id_orders", $pay->$id_orders)->delete();
      $tp = TanggunganPembayaran::where("id_users", $pay->id_users)->first();
      TanggunganPembayaran::where("id_users", $pay->id_users)
      ->update(["nominal" => $tp->nominal - $pay->nominal]);

      $logUang = LogUang::where("id_log_uang", $pay->id_orders);
      if ($logUang->count() > 0) {
        $lu = $logUang->first();
        $lu->nominal += $pay->nominal;
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

      $bill = Bill::where("id_orders", $pay->id_orders)->first();
      $bill->status = "2";
      $bill->save();
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
      "log_orders.users","log_orders.status_orders","tagihan",
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

  public function export_orders($from, $to, $find = null)
  {
    return (new Order($from, $to, $find))->download("Orders-".time().".xlsx");
  }

  public $from;
  public $to;
  public function summary_orders(Request $request)
  {
    $this->from = $request->from. " 00:00:00";
    $this->to = $request->to." 23:59:59";
    $orders = Orders::whereBetween("waktu_order", [$this->from, $this->to])->get();
    $piutang = 0;
    $totalOrders = 0;
    $modal = 0;
    $cash = 0;
    foreach ($orders as $p) {
      if ($p->piutang != null ) $piutang += $p->piutang->nominal;
      if ($p->id_status_orders >= 3) $totalOrders += $p->total_bayar;
    }

    $logUang = LogUang::whereBetween("waktu", [$this->from, $this->to])->get();
    foreach ($logUang as $lu) {
      if ($lu->status == 'in') $cash += $lu->nominal;
      else if($lu->status == 'out') $modal += $lu->nominal;
    }

    $this->selectedIDOrders = LogStokBarang::whereBetween("waktu", [$this->from, $this->to])
    ->pluck("id");
    $barang = Barang::with(["log_stok_barang" => function($query){
      $query
      ->select("id_barang",DB::raw("sum(jumlah) as jumlah"))
      ->where("status", "out")
      ->whereBetween("waktu", [$this->from, $this->to])
      ->groupBy("id_barang");
    }, "log_stok_barang.detail_orders" => function($query){
      $query
      ->select("id_orders","id_barang",DB::raw("sum(harga_beli*jumlah_barang) as total"))
      ->whereIn("id_orders", $this->selectedIDOrders)
      ->groupBy("id_barang");
    }])->get();
    return response([
      "modal" => $modal, "cash" => $cash,
      "total_orders" => $totalOrders, "piutang" => $piutang, "barang" => $barang
    ]);
  }

  public function get($id)
  {
    $o = Orders::where("id_orders",$id)->first();
    $itemDetail = array();
    foreach ($o->detail_orders as $d) {
      $package = array();
      $b = Barang::with("pack")->where("id_barang",$d->id_barang)->first();
      foreach ($b->pack as $p) {
        $itemPack = [
          "id_pack" => $p->id_pack,
          "nama_pack" => $p->pack->nama_pack,
          "keterangan" => $p->pack->keterangan,
          "harga" => $p->pack->harga,
          "kapasitas_kg" => $p->kapasitas_kg,
          "kapasitas_butir" => $p->kapasitas_butir
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
        "beli_pack" => ($d->harga_pack > 0) ? "1" : "",
        "is_lock" => ($d->is_lock) ? true : false
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
      "nama_kasir" => ($o->id_users == "") ? "" : $o->users->nama,
      "id_pembeli" => $o->id_pembeli,
      "nama_pembeli" => ($o->id_pembeli == "") ? "" : $o->pembeli->nama,
      "id_sopir" => $o->id_sopir,
      "nama_sopir" => ($o->id_sopir == "") ? "" : $o->sopir->nama,
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

  public function getSetoranUang($id_sopir)
  {
    return response(
      SetorUang::where("id_users",$id_sopir)->with(["pembeli","sopir"])
      ->orderBy("waktu","asc")->get()
    );
  }

  public function getSetoranPack($id_sopir)
  {
    return response(
      KembaliPack::where("id_users",$id_sopir)->with(["pack","sopir","pembeli"])
      ->orderBy("waktu","asc")->get()
    );
  }

  public function getKembaliOrders()
  {
    return response(
      KembaliOrders::with([
        "detail_kembali_orders","detail_kembali_orders.barang","detail_kembali_orders.pack",
        "orders","orders.pembeli","orders.sopir"
      ])->where("status","0")->orderBy("waktu","asc")->get()
    );
  }

  public function kembali_orders(Request $request)
  {
    $kembali = new KembaliOrders();
    $kembali->id_kembali_orders = "IKO".time().rand(1,1000);
    $kembali->id_orders = $request->id_orders;
    $kembali->id_sopir = $request->id_sopir;
    $kembali->waktu = date("Y-m-d H:i:s");
    $kembali->save();

    $detail_kembali_orders = json_decode($request->detail_kembali_orders);
    foreach ($detail_kembali_orders as $dk) {
      $detail = new DetailKembaliOrders();
      $detail->id_kembali_orders = $kembali->id_kembali_orders;
      $detail->id_barang = $dk->id_barang;
      $detail->jumlah_barang = $dk->jumlah_barang;
      $detail->save();
    }

    return response(["message" => "Data pengembalian order berhasil disimpan"]);
  }

  public function verify_kembali_orders(Request $request)
  {
    $kembali = KembaliOrders::where("id_kembali_orders", $request->id_kembali_orders)->first();
    $orders = Orders::where("id_orders", $kembali->id_orders)->first();

    $detail = json_decode($request->detail_kembali_orders);
    /***
    request -> id_kembali_orders, id_users
    format json:
    [{"id_barang": "IDB222", "jumlah_barang": "10", "id_supplier": "IDS9999"}]
    */
    foreach ($detail as $det) {
      /** edit detail_orders */
      $detailOrders = DetailOrders::where("id_orders", $kembali->id_orders)
      ->where("id_barang", $det->id_barang)->first();
       $jumlah_barang = $detailOrders->jumlah_barang - $det->jumlah_barang;
       $jumlah_pack = $detailOrders->jumlah_pack - $det->jumlah_pack;

       DetailOrders::where("id_orders", $kembali->id_orders)
       ->where("id_barang", $det->id_barang)
       ->update(["jumlah_barang" => $jumlah_barang,"jumlah_pack" => $jumlah_pack]);


      /** new log stok barang */
      $logStok = new LogStokBarang();
      $logStok->waktu = date("Y-m-d H:i:s");
      $logStok->event = "Pengembalian Stok Barang";
      $logStok->id_users = $request->id_users;
      $logStok->id_barang = $det->id_barang;
      $logStok->id_supplier = $det->id_supplier;
      $logStok->jumlah = $det->jumlah_barang;
      $logStok->id = $kembali->id_orders;
      $logStok->status = "in";

      // update stok_barang
      $stokBarang = StokBarang::where("id_supplier", $det->id_supplier)
      ->where("id_barang", $det->id_barang);
      if ($stokBarang->count() > 0) {
        $stok = $stokBarang->first();
        StokBarang::where("id_supplier", $det->id_supplier)
        ->where("id_barang", $det->id_barang)
        ->update(["stok" => $stok->stok + $det->jumlah_barang]);

        $logStok->stok = $stok->stok + $det->jumlah_barang;
      }else{
        $stok = new StokBarang();
        $stok->id_supplier = $det->id_supplier;
        $stok->id_barang = $det->id_barang;
        $stok->stok = $det->jumlah_barang;
        $stok->save();

        $logStok->stok = $det->jumlah_barang;
      }
      $logStok->save();

      /** update log stok Pack */
      if ($det->jumlah_pack > 0) {
        $logStok = new LogPack();
        $logStok->waktu = date("Y-m-d H:i:s");
        $logStok->jumlah = $det->jumlah_pack;
        $logStok->id_pack = $det->id_pack;
        $logStok->status = "in";
        $logStok->beli = false;
        $logStok->harga = 0;
        $logStok->id_pembeli = $orders->id_pembeli;
        $logStok->id_users = $orders->id_sopir;

        // update stok pack
        $stokPack = Pack::where("id_pack", $det->id_pack)->first();
        Pack::where("id_pack", $det->id_pack)
        ->update(["stok" => $stokPack->stok + $det->jumlah_pack]);

        $logStok->stok = $stokPack->stok + $det->jumlah_pack;
        $logStok->save();

        /** update tanggungan pack */
        $tp = TanggunganPack::where("id_users", $orders->id_pembeli)
        ->where("id_pack", $det->id_pack)->first();
        TanggunganPack::where("id_users", $orders->id_pembeli)->where("id_pack", $det->id_pack)
        ->update(["jumlah" => $tp->jumlah - $det->jumlah_pack]);
      }

    }

    /** get total_bayar */
    $detailOrders = DetailOrders::where("id_orders", $kembali->id_orders)->get();
    $total = 0;
    foreach ($detailOrders as $det) {
      $total += ($det->harga_beli * $det->jumlah_barang) + ($det->harga_pack * $det->jumlah_pack);
    }

    /** edit total_bayar pada Orders */
    $orders = Orders::where("id_orders", $kembali->id_orders)->first();
    $orders->total_bayar = $total;
    $orders->save();

    /** update bill */
    $bill = Bill::where("id_orders", $kembali->id_orders)->first();
    $bill->nominal = $orders->total_bayar - $orders->down_payment;
    $bill->save();

    /** drop kembali orders */
    // KembaliOrders::where("id_kembali_orders", $request->id_kembali_orders)->delete();
    // DetailKembaliOrders::where("id_kembali_orders", $request->id_kembali_orders)->delete();
    KembaliOrders::where("id_kembali_orders", $request->id_kembali_orders)
    ->update(["status" => "1"]);

    return response(["message" => "Data Pengembalian Order telah diverifikasi"]);
  }

  public function getReturOrder()
  {
    return response(
      ReturOrder::with([
        "detail_retur_order","detail_retur_order.barang","detail_retur_order.pack",
        "pembeli","sopir"
      ])->where("status","0")->orderBy("waktu_order","asc")->get()
    );
  }

  public function verify_retur_order(Request $request)
  {
    $err = [];
    try {
      $retur = ReturOrder::where("id_retur_order", $request->id_retur_order)->first();
      $detail = json_decode($request->detail_retur_order);
      /***
      request -> id_retur_order, id_users, id_pembeli, id_sopir, waktu_pengiriman
      format json:
      [{"id_barang": "IDB222", "jumlah_barang": "10", "id_supplier": "IDS9999"}]
      */

      /** create new id orders */
      $ido = "IDO".time().rand(1,1000);


      $total = 0;
      foreach ($detail as $det) {
        /** update detail retur */
        DetailReturOrder::where("id_retur_order", $request->id_retur_order)->where("id_barang", $det->id_barang)
        ->update(["jumlah_barang" => $det->jumlah_barang, "jumlah_pack" => $det->jumlah_pack]);

        /** create new detail_order */
        DetailOrders::create([
          "id_orders" => $ido,
          "id_barang" => $det->id_barang,
          "id_pack" => $det->id_pack_new,
          "jumlah_barang" => $det->jumlah_barang,
          "jumlah_pack" => $det->jumlah_pack_new,
          "harga_beli" => $det->harga_beli,
          "harga_pack" => $det->harga_pack,
          "is_lock" => $det->is_lock
        ]);

        /** count total */
        $total += ($det->harga_beli * $det->jumlah_barang) + ($det->harga_pack * $det->jumlah_pack);

        /** edit detail_orders */
        $detailOrders = DetailOrders::where("id_orders", $request->id_retur_order)
        ->where("id_barang", $det->id_barang)->first();
         $jumlah_barang = $detailOrders->jumlah_barang - $det->jumlah_barang;
         $jumlah_pack = $detailOrders->jumlah_pack - $det->jumlah_pack;

         DetailOrders::where("id_orders", $request->id_retur_order)
         ->where("id_barang", $det->id_barang)
         ->update(["jumlah_barang" => $jumlah_barang, "jumlah_pack" => $jumlah_pack]);


        /** update log stok barang */
        $logStok = new LogStokBarang();
        $logStok->waktu = date("Y-m-d H:i:s");
        $logStok->event = "Retur Barang";
        $logStok->id_users = $request->id_users;
        $logStok->id_barang = "IDB666";
        $logStok->id_supplier = $det->id_supplier;
        $logStok->jumlah = $det->jumlah_barang;
        $logStok->id = $retur->id_retur_order;
        $logStok->status = "in";

        // update stok_barang
        $stokBarang = StokBarang::where("id_supplier", $det->id_supplier)
        ->where("id_barang", "IDB666");
        if ($stokBarang->count() > 0) {
          $stok = $stokBarang->first();
          StokBarang::where("id_supplier", $det->id_supplier)
          ->where("id_barang", "IDB666")
          ->update(["stok" => $stok->stok + $det->jumlah_barang]);

          $logStok->stok = $stok->stok + $det->jumlah_barang;
        }else{
          $stok = new StokBarang();
          $stok->id_supplier = $det->id_supplier;
          $stok->id_barang = "IDB666";
          $stok->stok = $det->jumlah_barang;
          $stok->save();

          $logStok->stok = $det->jumlah_barang;
        }
        $logStok->save();

        /** update log stok Pack */
        if ($det->jumlah_pack > 0) {
          $logStok = new LogPack();
          $logStok->waktu = date("Y-m-d H:i:s");
          $logStok->jumlah = $det->jumlah_pack;
          $logStok->id_pack = $det->id_pack;
          $logStok->status = "in";
          $logStok->beli = ($det->harga_pack > 0) ? true : false;
          $logStok->harga = $det->harga_pack;
          $logStok->id_pembeli = $request->id_pembeli;
          $logStok->id_users = $request->id_sopir;

          // update stok pack
          $stokPack = Pack::where("id_pack", $det->id_pack)->first();
          Pack::where("id_pack", $det->id_pack)
          ->update(["stok" => $stokPack->stok + $det->jumlah_pack]);

          $logStok->stok = $stokPack->stok + $det->jumlah_pack;
          $logStok->save();

          /** update tanggungan pack */
          $tp = TanggunganPack::where("id_users", $request->id_pembeli)
          ->where("id_pack", $det->id_pack)->first();
          TanggunganPack::where("id_users", $request->id_pembeli)->where("id_pack", $det->id_pack)
          ->update(["jumlah" => $tp->jumlah - $det->jumlah_pack]);
        }
      }

      /** get total_bayar */
      // $detailOrders = DetailOrders::where("id_orders", $kembali->id_orders)->get();
      // $total = 0;
      // foreach ($detailOrders as $det) {
      //   $total += ($det->harga_beli * $det->jumlah_barang) + ($det->harga_pack * $det->jumlah_pack);
      // }

      array_push($err,"foreach end");
      /** edit total_bayar pada Orders */
      $orders = Orders::where("id_orders", $retur->id_retur_order)->first();
      $orders->total_bayar = $orders->total_bayar - $total;
      $orders->save();

      array_push($err,"total orders saved");

      /** update bill */
      $bill = Bill::where("id_orders", $retur->id_retur_order)->first();
      $bill->nominal = $orders->total_bayar - $orders->down_payment;
      $bill->save();

      array_push($err,"bill saved");

      /** drop kembali orders */
      // KembaliOrders::where("id_kembali_orders", $request->id_kembali_orders)->delete();
      // DetailKembaliOrders::where("id_kembali_orders", $request->id_kembali_orders)->delete();

      /** update status retur */
      ReturOrder::where("id_retur_order", $request->id_retur_order)->update(["status" => "1", "total_bayar" => $total]);

      /** create new orders */
      Orders::create([
        "id_orders" => $ido,
        "id_pembeli" => $retur->id_pembeli,
        "id_users" => $request->id_users,
        "waktu_order" => date("Y-m-d H:i:s"),
        "waktu_pengiriman" => $request->waktu_pengiriman,
        "po" => $retur->po, "invoice" => $retur->invoice,
        "id_status_orders" => "2",
        "tgl_jatuh_tempo" => $retur->tgl_jatuh_tempo,
        "tipe_pembayaran" => $retur->tipe_pembayaran,
        "total_bayar" => $total,
        "id_sopir" => $request->id_sopir,
        "down_payment" => $retur->down_payment,
        "catatan" => "Retur Barang",
        "kendala" => ""
      ]);

      array_push($err,"order created");

      return response(["message" => "Data Retur Order telah diverifikasi"]);
    } catch (\Exception $e) {
      return response(["message" => $e->getMessage(),"log" => $err]);
    }

  }


  public function grafik(Request $request)
  {
    $from = $request->from." 00:00:00";
    $to = $request->to." 23:59:59";
    $data = Orders::whereBetween("waktu_order",[$from, $to])
    ->select(DB::raw("date(waktu_order) as waktu"), DB::raw("sum(total_bayar) as total"))
    ->groupBy(DB::raw("date(waktu_order)"))->get();
    return response($data);
  }

  public function struk($id_orders)
  {
    $orders = Orders::where("id_orders", $id_orders)->with([
      "detail_orders","pembeli","pembeli.tanggungan_pack","pembeli.tanggungan_pack.pack",
      "detail_orders.barang","detail_orders.pack","users"
    ]);

    $hari=["Senin","Selasa","Rabu","Kamis","Jumat","Sabtu","Minggu"];

    if ($orders->count() > 0) {
      $o = $orders->first();
      $doc= new FPDF("P","cm",array("7.6","29.7"));
      $doc->AddPage();
      // $doc->AddFont('A11','','ufonts.com_bm-receipt-a11.php');
      $doc->AddFont('A11','','fake receipt.php');
      $doc->AddFont('Arial','','ufonts.com_arial.php');
      $doc->SetMargins(0.1,0.1);
      $doc->SetTopMargin(0.1);
      $doc->SetTitle("Fine Farm ID");

      $doc->SetFont('Arial','B',18);
      $doc->Cell(7,0.1,'',0,1,'C');
      $doc->Cell(7,0.5,'FINE FARM',0,1,'C');
      $doc->SetFont('Arial','',9);
      $doc->Cell(7,0.5,'EGG SUPPLIER',0,1,'C');
      $doc->SetFont('A11','',10);
      $doc->Cell(7,0.5,'JL. CILIWUNG 11, MALANG',0,1,'C');
      $doc->SetFont('A11','',9);
      $doc->Cell(7,0.5,'0818-383038',0,1,'C');
      //$doc->Cell(7,0.5,'','B',1,'C');
      $doc->SetFont('A11','',6);
      $doc->Ln(0.5);
      //$doc->Cell(0.1);

      $tgl1=date_create($o->waktu_pengiriman);
      $doc->Cell(7,0.8,$hari[date_format($tgl1,'N')-1]." ".date_format($tgl1,'d/m/Y'),'B',1,'L');

      $doc->Cell(2.2,0.8,'Kepada',0,0,'L');
      $doc->Cell(4.8,0.8,": ".$o->pembeli->nama,0,1,'L');

      $doc->Cell(2.2,0.5,'No. PO',0,0,'L');
      $doc->Cell(4.8,0.5,": $o->po",0,1,'L');

      $doc->Cell(2.2,0.5,'Invoice',0,0,'L');
      $doc->Cell(4.8,0.5,": $o->invoice",0,1,'L');

      //$doc->Cell(2,0.5,'Tanggal',0,0,'L');


      $doc->Cell(2.2,0.5,'Jth Tempo',0,0,'L');
      $tgl1=date_create($o->tgl_jatuh_tempo);
      $doc->Cell(5,0.5,": ".date_format($tgl1,'d/m/Y'),0,1,'L');

      $doc->Cell(2.2,0.8,'Kasir','B',0,'L');
      $doc->Cell(4.8,0.8,": ".$o->users->nama,'B',1,'L');

      $doc->Ln(0.5);

      foreach ($o->detail_orders as $key => $value) {
        $doc->Cell(4,0.7,$value->barang->nama_barang." (".$value->pack->nama_pack.")",0,1,'L');
        $doc->Cell(1.5,0.7,
        $value->jumlah_barang." ".($value->barang->satuan == "1" ? "Kg" : "Btr."),
        0,0,'L');
        //$doc->Cell(0.3,0.7,'x',0,0,'R');
        $doc->Cell(1.5,0.7," x ".number_format($value->harga_beli,0,',','.'),0,0,'R');
        //$doc->Cell(0.3,0.7,'=',0,0,'R');
        $doc->Cell(3.5,0.7," = ".number_format($value->harga_beli * $value->jumlah_barang,
        0,',','.'),0,1,'R');
      }

      //$doc->Cell(1);
      $doc->SetFont('A11','',9);
      $doc->Ln(0.5);
      $doc->Cell(2,0.8,'Total: ','T',0,'L');
      $doc->Cell(4.5,0.8,"Rp ".number_format($o->total_bayar,0,',','.'),'T',0,'R');
      $doc->Cell(0.9,0.8,'','T',1,'L');
      $doc->SetFont('A11','',6);

      $doc->Ln(0.5);
      $countTP = 0;
      foreach ($o->pembeli->tanggungan_pack as $item) {
        $countTP += $item->jumlah;
      }
      if ($countTP > 0) {
        $doc->Cell(7,0.8,'Tanggungan Pack',0,1,'C');
        $doc->Cell(3,0.8,'Nama Pack','B',0,'L');
        $doc->Cell(2,0.8,'Jumlah','B',0,'C');
        $doc->Cell(2,0.8,'Kembali','B',1,'C');
        foreach ($o->pembeli->tanggungan_pack as $key => $value) {
          if ($value->jumlah > 0) {
            $doc->Cell(3,0.8,$value->pack->nama_pack,'0',0,'L');
            $doc->Cell(2,0.8,$value->jumlah,'0',1,'C');
          }
        }
      }
      $doc->Ln(0.5);
      $doc->Cell(3.5,0.5,'Penerima','0',0,'C');
      $doc->Cell(3.5,0.5,'Pengirim','0',0,'C');
      $doc->Ln(3);

      $doc->Cell(3,0.3,'         ','B',0,'C');
      $doc->Cell(1,0.3,'         ','0',0,'L');
      $doc->Cell(3,0.3,'         ','B',0,'C');
      $doc->Output("$o->invoice.pdf","D");
    }else{
      return "Invalid Orders";
    }

  }

  public function kuitansi($id_pembeli, $from, $to)
  {
    $from = $from." 00:00:00";
    $to = $to." 23:59:59";
    $orders = Orders::whereBetween("waktu_order", [$from, $to])
    ->where("id_pembeli",$id_pembeli)->where("id_status_orders",">","0")
    ->with([
      "detail_orders","detail_orders.barang","pembeli"
    ])->orderBy("waktu_order","asc")->get();

    $total = 0;
    $pembeli = "";
    $data = [];
    foreach ($orders as $o) {
      $pembeli = $o->pembeli->nama;
      foreach ($o->detail_orders as $do) {
        $item = [
          "tanggal" => $this->convertDate($o->waktu_order),
          "customer" => $o->pembeli->nama,
          "harga" => number_format($do->harga_beli,0,",","."),
          "qty" => $do->jumlah_barang,
          "total" => number_format($do->harga_beli * $do->jumlah_barang,0,",",".")
        ];
        array_push($data, $item);
        $total += ($do->harga_beli * $do->jumlah_barang);
      }
    }

    $terbilang = $this->terbilang($total). " rupiah";

    $pdf = PDF::loadview('kuitansi',[
      "total" => number_format($total,0,",","."),
      "pembeli" => $pembeli,
      "terbilang" => $terbilang,
      "data" => $data,
      "sekarang" => $this->convertDate(date("Y-m-d"))
    ]);
    return $pdf->download('Kuitansi-'.time());
  }

  public function penyebut($nilai) {
		$nilai = abs($nilai);
		$huruf = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
		$temp = "";
		if ($nilai < 12) {
			$temp = " ". $huruf[$nilai];
		} else if ($nilai <20) {
			$temp = $this->penyebut($nilai - 10). " belas";
		} else if ($nilai < 100) {
			$temp = $this->penyebut($nilai/10)." puluh". $this->penyebut($nilai % 10);
		} else if ($nilai < 200) {
			$temp = " seratus" . $this->penyebut($nilai - 100);
		} else if ($nilai < 1000) {
			$temp = $this->penyebut($nilai/100) . " ratus" . $this->penyebut($nilai % 100);
		} else if ($nilai < 2000) {
			$temp = " seribu" . $this->penyebut($nilai - 1000);
		} else if ($nilai < 1000000) {
			$temp = $this->penyebut($nilai/1000) . " ribu" . $this->penyebut($nilai % 1000);
		} else if ($nilai < 1000000000) {
			$temp = $this->penyebut($nilai/1000000) . " juta" . $this->penyebut($nilai % 1000000);
		} else if ($nilai < 1000000000000) {
			$temp = $this->penyebut($nilai/1000000000) . " milyar" . $this->penyebut(fmod($nilai,1000000000));
		} else if ($nilai < 1000000000000000) {
			$temp = $this->penyebut($nilai/1000000000000) . " trilyun" . $this->penyebut(fmod($nilai,1000000000000));
		}
		return $temp;
	}

	public function terbilang($nilai) {
		if($nilai<0) {
			$hasil = "minus ". trim($this->penyebut($nilai));
		} else {
			$hasil = trim($this->penyebut($nilai));
		}
		return $hasil;
	}

  public function convertDate($tanggal){
    $bulan = array (
  		1 =>   'Januari',
  		'Februari',
  		'Maret',
  		'April',
  		'Mei',
  		'Juni',
  		'Juli',
  		'Agustus',
  		'September',
  		'Oktober',
  		'November',
  		'Desember'
  	);
    $onlyDate = explode(' ', $tanggal);
  	$pecahkan = explode('-', $onlyDate[0]);

  	// variabel pecahkan 0 = tanggal
  	// variabel pecahkan 1 = bulan
  	// variabel pecahkan 2 = tahun

  	return $pecahkan[2] . ' ' . $bulan[ (int)$pecahkan[1] ] . ' ' . $pecahkan[0];
  }

  public function kendala(Request $request)
  {
    try {
      $id_orders = $request->id_orders;
      $orders = Orders::where("id_orders", $id_orders)->first();
      $orders->kendala = $request->kendala;
      $orders->save();
      return response(["message" => "Kendala berhasil disimpan"]);
    } catch (\Exception $e) {
      return response(["message" => $e->getMessage()]);
    }
  }

  public function get_kembali_orders(Request $request, $limit = 5, $offset = 0)
  {
    $from = $request->from;
    $to = $request->to;
    $kembali = KembaliOrders::whereBetween("waktu", [$from, $to])
    ->where("status","1")->orderBy("waktu","asc")
    ->with([
      "orders","detail_kembali_orders","detail_kembali_orders.barang",
      "detail_kembali_orders.pack","orders.pembeli","orders.users","sopir"
    ]);
    return response([
      "kembali_orders" => $kembali->take($limit)->skip($offset)->get(),
      "count" => $kembali->count()
    ]);
  }

  public function get_retur_orders(Request $request, $limit = 5, $offset = 0)
  {
    $from = $request->from;
    $to = $request->to;
    $retur = ReturOrder::whereBetween("waktu_pengiriman", [$from, $to])
    ->where("status","1")->orderBy("waktu_pengiriman","asc")
    ->with([
      "detail_retur_order","detail_retur_order.barang",
      "detail_retur_order.pack","pembeli","users","sopir"
    ]);
    return response([
      "retur_orders" => $retur->take($limit)->skip($offset)->get(),
      "count" => $retur->count()
    ]);
  }
}

 ?>
