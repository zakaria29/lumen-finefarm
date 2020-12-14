<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Kuitansi</title>
    <style media="screen">
    .currency {
      text-align: right;
    }

    .currency:before {
      content: "Rp";
      float: left;
      padding-right: 4px;
    }
    </style>
  </head>
  <body>
    <h2 style="margin-bottom:2px;" align="center"> <u>Kuitansi</u> </h2>
    <h5 style="margin-top:1px;" align="center">No. <?php echo date("Ymd"); ?></h5>
    <br />
    <table>
      <tr>
        <td>Akan diterima dari</td>
        <td>: <strong><?php echo $pembeli; ?></strong> </td>
      </tr>
      <tr>
        <td>Dengan uang sejumlah</td>
        <td>: <?php echo ucwords($terbilang); ?></td>
      </tr>
      <tr>
        <td>Untuk Pembayaran</td>
        <td>: Pembelian Telur dengan nota sebagai berikut</td>
      </tr>
    </table>
    <br />
    <table style="border-collapse:collapse" cellpadding="2" border="1" width="100%">
      <thead>
        <tr>
          <th align="center">No</th>
          <th align="center">Tanggal</th>
          <th align="center">Customer</th>
          <th align="center">@</th>
          <th align="center">Qty</th>
          <th align="center">Total</th>
        </tr>
      </thead>
      <tbody>
        <?php $qty = 0; $i = 1; foreach ($data as $d): ?>
          <tr>
            <td align="center"><?php echo $i; ?></td>
            <td align="center"><?php echo $d["tanggal"]; ?></td>
            <td align="center"><?php echo $d["customer"]; ?></td>
            <td> <div class="currency"><?php echo $d["harga"]; ?></div> </td>
            <td align="center"><?php echo $d["qty"]; ?></td>
            <td> <div class="currency"><?php echo $d["total"]; ?></div> </td>
          </tr>
        <?php
          $qty +=$d["qty"];
          $i++;
          endforeach; ?>
        <tr>
          <td colspan="4" align="center">Total</td>
          <td align="center"><?php echo $qty; ?></td>
          <td> <div class="currency"><?php echo $total; ?></div> </td>
        </tr>
      </tbody>
    </table>
    <br>
    <br>
    Jumlah Pembayaran: <strong style="font-size:18px;"><?php echo "Rp ".$total; ?></strong>
    <br>
    <u>Nb. Pembayaran Via Transfer</u> <br>
    &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; BCA <br>
    &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Gafur Feriyanto atau Lina Kinayu <br>
    &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; No.Rek. 8160717719 <br>

    <div style="width:100%; text-align:right">
      Malang, <?php echo $sekarang; ?>
      <br><br><br><br><br>
      (Gafur Feriyanto)
    </div>

  </body>
</html>
