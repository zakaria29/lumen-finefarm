<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->get("/pack", "PackController@get_all");
$router->get("/pack/{limit}/{offset}", "PackController@get");
$router->post("/pack/save", "PackController@store");
$router->post("/pack/update", "PackController@update");
$router->post("/pack/kapasitas", "PackController@store_kapasitas");
$router->delete("/pack/drop/{id_pack}", "PackController@drop");

$router->get("/barang", "BarangController@get_all");
$router->get("/barang/{limit}/{offset}", "BarangController@get");
$router->post("/barang/save", "BarangController@store");
$router->post("/barang/update", "BarangController@update");
$router->delete("/barang/drop/{id_barang}", "BarangController@drop");
$router->post("/barang/update_harga", "BarangController@update_harga");
$router->get("/barang_export", "BarangController@export");

$router->get("/supplier", "SupplierController@get_all");
$router->get("/supplier/{limit}/{offset}", "SupplierController@get");
$router->post("/supplier/{limit}/{offset}", "SupplierController@find");
$router->post("/supplier/save", "SupplierController@store");
$router->post("/supplier/update", "SupplierController@update");
$router->delete("/supplier/drop/{id_supplier}", "SupplierController@drop");

$router->get("/supply", "SupplyController@get_all");
$router->get("/supply/get/{id_supply}", "SupplyController@get");
$router->post("/supply/{limit}/{offset}", "SupplyController@find");
$router->post("/supply/save", "SupplyController@store");
$router->post("/supply/update", "SupplyController@update");
$router->delete("/supply/drop/{id_supply}", "SupplyController@drop");

$router->get("/owner", "OwnerController@get_all");
$router->get("/owner/{limit}/{offset}", "OwnerController@get");
$router->post("/owner/{limit}/{offset}", "OwnerController@find");
$router->post("/owner/save", "OwnerController@store");
$router->post("/owner/update", "OwnerController@update");
$router->delete("/owner/drop/{id_users}", "OwnerController@drop");
$router->post("/owner/auth", "OwnerController@auth");
$router->post("/owner/check", "OwnerController@check");
$router->post("/owner/dashboard", "OwnerController@dashboard");

$router->get("/cashier", "CashierController@get_all");
$router->get("/cashier/{limit}/{offset}", "CashierController@get");
$router->post("/cashier/{limit}/{offset}", "CashierController@find");
$router->post("/cashier/save", "CashierController@store");
$router->post("/cashier/update", "CashierController@update");
$router->delete("/cashier/drop/{id_users}", "CashierController@drop");
$router->post("/cashier/auth", "CashierController@auth");
$router->post("/cashier/check", "CashierController@check");
$router->post("/cashier/dashboard", "CashierController@dashboard");
$router->post("/cashier/profil", "CashierController@edit_profil");

$router->get("/driver", "DriverController@get_all");
$router->post("/driver", "DriverController@get_all");
$router->get("/driver/{limit}/{offset}", "DriverController@get");
$router->post("/driver/{limit}/{offset}", "DriverController@find");
$router->post("/driver/save", "DriverController@store");
$router->post("/driver/update", "DriverController@update");
$router->delete("/driver/drop/{id_users}", "DriverController@drop");
$router->post("/driver/auth", "DriverController@auth");
$router->post("/driver/check", "DriverController@check");
$router->post("/driver/profil", "DriverController@edit_profil");
$router->post("/driver/dashboard", "DriverController@dashboard");
$router->get("/kembali-pack/{id_users}","DriverController@kembali_pack");
$router->post("/kembali-pack", "DriverController@store_kembali_pack");
$router->delete("/kembali-pack/{id_pembeli}/{id_pack}", "DriverController@drop_kembali_pack");
$router->get("/setor-uang/{id_users}","DriverController@setor_uang");
$router->post("/setor-uang","DriverController@store_setor_uang");
$router->delete("/setor-uang/{id_users}/{id_pembeli}", "DriverController@drop_setor_uang");

$router->get("/customer", "CustomerController@get_all");
$router->get("/group_customer", "CustomerController@get_group_customer");
$router->get("/customer/{limit}/{offset}", "CustomerController@get");
$router->post("/customer/{limit}/{offset}", "CustomerController@find");
$router->post("/customer/save", "CustomerController@store");
$router->post("/customer/update", "CustomerController@update");
$router->delete("/customer/drop/{id_users}", "CustomerController@drop");
$router->post("/customer/auth", "CustomerController@auth");
$router->post("/customer/check", "CustomerController@check");
$router->post("/customer/dashboard", "CustomerController@dashboard");
$router->post("/customer-orders/{id}[/{limit}/{offset}]", "CustomerController@orders");
$router->get("/customer-bill/{id_users}", "CustomerController@get_bill");
$router->get("/customer-pack", "CustomerController@tanggungan_pack");
$router->get("/customer-tagihan", "CustomerController@tanggungan_pembayaran");
$router->post("/lock-pack-barang", "CustomerController@store_lock_pack_barang");
$router->post("/customer/profil", "CustomerController@edit_profil");

$router->get("/orders/get/{id_orders}", "OrdersController@get");
$router->post("/orders/new_order", "OrdersController@create_new_order");
$router->post("/orders/update_order", "OrdersController@update_order");
$router->post("/orders/{limit}/{offset}", "OrdersController@searching");
$router->post("/verify-orders/{limit}/{offset}", "OrdersController@verify_order");
$router->post("/accept-orders/{id}", "OrdersController@accept_order");
$router->post("/prepare-orders/{limit}/{offset}", "OrdersController@prepare_order");
$router->post("/send-orders/{id_orders}", "OrdersController@send_order");
$router->post("/kirim-orders", "OrdersController@kirim_order");
$router->post("/ready-send-orders/{id}/{limit}/{offset}", "OrdersController@ready_send_order");
$router->get("/deliver-orders/{id_orders}/{id_users}", "OrdersController@deliver_order");
$router->post("/coming-orders/{id}/{limit}/{offset}", "OrdersController@coming_order");
$router->post("/delivered-orders/{id_orders}", "OrdersController@delivered_order");
$router->get("/setoran-uang/{id_sopir}", "OrdersController@getSetoranUang");
$router->post("/verify-uang","OrdersController@verify_uang");
$router->get("/setoran-pack/{id_sopir}", "OrdersController@getSetoranPack");
$router->post("/verify-pack","OrdersController@verify_pack");
$router->post("/pay-orders","OrdersController@pay_orders");
$router->get("/verify-pembayaran", "OrdersController@get_verify_pembayaran");
$router->post("/verify-pembayaran","OrdersController@verify_pembayaran");
$router->post("/summary-orders","OrdersController@summary_orders");
$router->post("/grafik","OrdersController@grafik");

$router->post("/mutasi-pack/{id_pack}","PackController@mutasi_pack");
$router->post("/mutasi-stok/{id_barang}","BarangController@mutasi_stok");
$router->get("/struk/{id_orders}","OrdersController@struk");

$router->get("/kembali-orders","OrdersController@getKembaliOrders");
$router->post("/kembali-orders","OrdersController@kembali_orders");
$router->post("/verify-kembali-orders","OrdersController@verify_kembali_orders");
