<?php
require 'koneksi.php';

$created = [];
foreach (array_keys($mapping) as $catName) {
    $q = $mysqli->prepare('SELECT id_kategori FROM kategori WHERE nama_kategori = ? LIMIT 1');
    if (!$q) { echo "DB error: " . $mysqli->error; exit; }
    $q->bind_param('s', $catName);
    $q->execute();
    $res = $q->get_result();
    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $created[$catName] = $row['id_kategori'];
    } else {
        $ins = $mysqli->prepare('INSERT INTO kategori (nama_kategori, deskripsi) VALUES (?, ?)');
        $desc = 'Auto-created category ' . $catName;
        $ins->bind_param('ss', $catName, $desc);
        $ins->execute();
        $created[$catName] = $mysqli->insert_id;
    }
}

$menus = [];
$res = $mysqli->query('SELECT id_menu, nama_menu, deskripsi FROM menu');
if ($res) {
    while ($r = $res->fetch_assoc()) $menus[] = $r;
}

$updated = 0;
$skipped = 0;
$details = [];

foreach ($menus as $m) {
    $name = strtolower($m['nama_menu'] ?? '');
    $desc = strtolower($m['deskripsi'] ?? '');
    $assigned = null;
    foreach ($mapping as $catName => $keywords) {
        if (empty($keywords)) continue;
        foreach ($keywords as $kw) {
            if ($kw === '') continue;
            if (strpos($name, $kw) !== false || strpos($desc, $kw) !== false) {
                $assigned = $created[$catName];
                break 2;
            }
        }
    }
    if ($assigned === null) {
        $assigned = $created['Uncategorized'];
        $skipped++;
    }

    $u = $mysqli->prepare('UPDATE menu SET id_kategori = ? WHERE id_menu = ?');
    $u->bind_param('ii', $assigned, $m['id_menu']);
    if ($u->execute()) {
        $updated++;
        $details[] = [ 'id_menu'=>$m['id_menu'], 'name'=>$m['nama_menu'], 'kategori_id'=>$assigned ];
    }
}

?><!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Assign Categories</title>
<style>body{font-family:Arial,Helvetica,sans-serif;padding:18px}table{border-collapse:collapse;width:100%}td,th{border:1px solid #ddd;padding:8px}</style>
</head>
<body>
  <h1>Assign Categories</h1>
  <p>Categories created/used: <?php echo count($created); ?></p>
  <p>Menus processed: <?php echo count($menus); ?> — updated: <?php echo $updated; ?> — assigned default: <?php echo $skipped; ?></p>
  <table>
    <thead><tr><th>ID</th><th>Nama Menu</th><th>Assigned Category ID</th></tr></thead>
    <tbody>
    <?php foreach($details as $d): ?>
      <tr>
        <td><?php echo htmlspecialchars($d['id_menu'])?></td>
        <td><?php echo htmlspecialchars($d['name'])?></td>
        <td><?php echo htmlspecialchars($d['kategori_id'])?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <p><a href="data_master.php">Back to Data Master</a></p>
</body>
</html>
