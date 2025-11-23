<?php
// checkout_success.php
$order_code = $_GET['code'] ?? '';
if (!$order_code) die("Kode pesanan tidak ditemukan.");

$file = "orders.json";
if(!file_exists($file)) die("Data pesanan tidak ada!");

$orders = json_decode(file_get_contents($file), true);

$found = null;
foreach($orders as $o){
    if(isset($o['order_code']) && $o['order_code'] === $order_code){
        $found = $o;
        break;
    }
}

if(!$found) die("Pesanan tidak ditemukan!");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nota Pesanan</title>
    <style>
        body{
            font-family:Arial;
            background:#f7f7f7;
            margin:0;
            padding:20px;
        }
        
        .nota{
            max-width:500px;
            margin:auto;
            background:#fff;
            padding:20px;
            border-radius:10px;
            box-shadow:0 4px 20px rgba(0,0,0,0.1);
        }
        
        h2{
            text-align:center;
        }
        
        table{
            width:100%;
            margin-top:10px;
        }
        
        td,th{
            padding:5px;
            font-size:14px;
        }
        
        .total{
            font-weight:bold;
        }
        
        .center{
            text-align:center;
        }
        
        .code{
            font-size:18px;color:#d9534f;
            font-weight:bold;
        }
        a{
            display:inline-block;
            background:#0275d8;
            color:#fff;
            padding:10px 20px;
            border-radius:6px;
            text-decoration:none;
            margin-top:15px;
            font-weight:bold;
        }
    </style>
</head>
<body>
<div class="nota">
    <h2>Nota Pesanan</h2>
    <p><strong>Kode Pesanan:</strong> <span class="code"><?= $found['order_code']?></span></p>
    <p><strong>Nama:</strong> <?= $found['customer']?></p>
    <p><strong>Meja:</strong> <?= $found['table']?></p>
    <hr>

    <table>
        <?php $total = 0; ?>
        <?php foreach($found['items'] as $it): 
            $subtotal = $it['price'] * $it['qty'];
            $total += $subtotal;
        ?>
        <tr>
            <td><?= $it['title']?> (x<?= $it['qty']?>)</td>
            <td>Rp <?= number_format($subtotal,0,',','.')?></td>
        </tr>
        <?php endforeach; ?>
        <tr class="total">
            <td>Total</td>
            <td>Rp <?= number_format($total,0,',','.')?></td>
        </tr>
    </table>

    <p class="center">Terima kasih telah memesan!</p>
        <p class="center">
        <a href="index.php">Kembali ke Menu</a>
    </p>
</div>
</body>
</html>
