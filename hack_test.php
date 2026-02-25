<?php
$tk = "your_token"; $src = $_SERVER['DOCUMENT_ROOT']; $sz = 200; // 配置：Token、源路徑、每批檔案數
if (($_SERVER['HTTP_AUTHORIZATION'] ?? '') !== "Bearer $tk") die("!"); // 身份驗證

$p = (int)($_GET['part'] ?? 1); $all = []; // 獲取分片編號
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($src, 4096)); // 4096 = SKIP_DOTS
foreach ($it as $f) if ($f->isFile()) $all[] = $f->getRealPath(); // 遞迴掃描檔案
sort($all); $chunk = array_slice($all, ($p - 1) * $sz, $sz); // 取得當前批次

if (!$chunk) { header("HTTP/1.1 404 Not Found"); die(); } // 無檔案則回傳 404

$tmp = sys_get_temp_dir() . "/b$p.zip"; $z = new ZipArchive; // 暫存檔與 Zip 物件
if ($z->open($tmp, 8) !== true) die("z"); // 8 = ZipArchive::CREATE
foreach ($chunk as $f) $z->addFile($f, ltrim(str_replace($src, '', $f), '/\\')); // 加入壓縮檔
$z->close();

header("Content-Type: application/zip");
header("Content-Length: " . filesize($tmp));
readfile($tmp); unlink($tmp); // 輸出並立刻刪除暫存檔
?>