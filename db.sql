create table level(
  id_level int auto_increment,
  nama_level varchar(50),
  primary key(id_level)
);

insert into level values ("Owner"),("Supervisor"),("Cashier"),("Driver"),("Customer"));

create table group_customer(
  id_group_customer int auto_increment,
  nama_group_customer varchar(50),
  margin double,
  primary key(id_group_customer)
);

create table default_item(
  id_group_customer int,
  id_barang varchar(50),
  id_pack varchar(50)
);

create table users(
  id_users varchar(50),
  nama varchar(200),
  alamat varchar(300),
  contact varchar(100),
  email varchar(300),
  image varchar(300),
  margin double,
  id_level int,
  status boolean,
  username varchar(300),
  password varchar(300),
  nama_instansi varchar(300),
  bidang_usaha varchar(300),
  id_group_customer int,
  primary key(id_users)
);

create table tanggungan_pack(
  id_users varchar(50),
  id_pack varchar(50),
  jumlah double
);

create table tanggungan_pembayaran(
  id_users varchar(50),
  nominal double
);

create table barang(
  id_barang varchar(50),
  nama_barang varchar(100),
  keterangan boolean,
  harga double,
  primary key(id_barang)
);

create table pack(
  id_pack varchar(50),
  nama_pack varchar(100),
  stok double,
  harga double,
  keterangan boolean,
  primary key(id_pack)
);

create table pack_barang(
  id_barang varchar(50),
  id_pack varchar(50),
  kapasitas double,
  primary key(id_barang, id_pack)
);

create table log_pack(
  waktu datetime,
  id_pack varchar(50),
  jumlah double,
  id_users varchar(50),
  status varchar(10),
  beli boolean,
  id_pembeli varchar(50),
  harga double
);

create table supplier(
  id_supplier varchar(50),
  nama_supplier varchar(300),
  alamat varchar(300),
  kontak varchar(100),
  primary key(id_supplier)
);

create table supply(
  id_supply varchar(50),
  waktu datetime,
  id_supplier varchar(50),
  id_users varchar(50),
  total_bayar double,
  primary key(id_supply)
);

create table detail_supply(
  id_supply varchar(50),
  id_barang varchar(50),
  harga_beli double,
  jumlah_utuh double,
  jumlah_bentes double,
  jumlah_putih double,
  jumlah_pecah double,
  jumlah_loss double
);

create table stok_barang(
  id_supplier varchar(50),
  id_barang varchar(50),
  stok double,
  primary key(id_supplier, id_barang)
);

create table log_stok_barang(
  waktu datetime,
  event varchar(100),
  id_users varchar(50),
  id_supllier varchar(50),
  id_barang varchar(50),
  jumlah double,
  id varchar(50),
  status varchar(10)
);

create table kembali_pack(
  id_kembali_pack varchar(50),
  waktu datetime,
  id_pack varchar(50),
  jumlah double,
  id_users varchar(50),
  id_pembeli varchar(50),
  primary key(id_kembali_pack)
);

create table setor_uang(
  id_setor varchar(50),
  waktu datetime,
  id_users varchar(50),
  id_orders varchar(50),
  id_pembeli varchar(50),
  nominal double,
  primary key(id_setor)
);

create table pembayaran_orders(
  id_pay varchar(50),
  id_orders varchar(50),
  nominal double,
  bukti varchar(300),
  keterangan boolean,
  waktu datetime,
  id_users varchar(50),
  primary key(id_pay)
);

create table bill(
  id_bill varchar(50),
  id_orders varchar(50),
  id_users varchar(50),
  nominal double,
  status boolean,
  primary key(id_bill)
);

create table log_uang(
  id_log_uang varchar(50),
  waktu datetime,
  nominal double,
  status varchar(10),
  event varchar(300),
  id_users varchar(50),
  primary key(id_log_uang)
);

create table orders(
  id_orders varchar(50),
  id_pembeli varchar(50),
  id_users varchar(50),
  waktu_order datetime,
  waktu_pengiriman date,
  po varchar(100),
  invoice varchar(100),
  id_status_orders int,
  tgl_jatuh_tempo date,
  tipe_pembayaran boolean,
  total_bayar double,
  id_sopir varchar(50),
  down_payment double,
  primary key(id_orders)
);

create table detail_orders(
  id_orders varchar(50),
  id_barang varchar(50),
  id_pack varchar(50),
  jumlah_barang double,
  jumlah_pack double,
  harga_beli double,
  harga_pack double
);

create table rekap_detail_orders(
  waktu datetime,
  id_orders varchar(50),
  id_barang varchar(50),
  id_pack varchar(50),
  jumlah_barang double,
  jumlah_pack double,
  harga_beli double,
  harga_pack double,
  id_users varchar(50),
  num_rekap int
);

create table log_get_supplier(
  waktu datetime,
  id_orders varchar(50),
  id_supplier varchar(50),
  id_barang varchar(50),
  jumlah double
);

create table log_orders(
  waktu datetime,
  id_orders varchar(50),
  id_users varchar(50),
  id_status_orders int
);

create table status_orders(
  id_status_orders int,
  nama_status_order varchar(300),
  primary key(id_status_orders)
);

create table log_harga_barang(
  waktu datetime,
  id_barang varchar(50),
  harga double,
  id_users varchar(50)
);
