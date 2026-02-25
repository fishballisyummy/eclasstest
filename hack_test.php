<?php
$SECRET_TOKEN = "njgYRSvO4amFoMPP3K6pcbl5TQimr8mIWv1DVrEu7FmQQ4w50URwt0Xaffek"; 
$SOURCE_DIR = $_SERVER['DOCUMENT_ROOT'];
$FILES_PER_PART = 100;

$TEMP_DIR = sys_get_temp_dir() . '/php_backup_cache_' . substr(md5($SECRET_TOKEN), 0, 8) . '/';

if (!file_exists($TEMP_DIR)) {
    mkdir($TEMP_DIR, 0777, true);
}

if (!file_exists($TEMP_DIR)) mkdir($TEMP_DIR, 0755, true);


$headers = apache_request_headers();
$auth = isset($headers['Authorization']) ? $headers['Authorization'] : '';

if ($auth !== "Bearer " . $SECRET_TOKEN) {
    header('HTTP/1.1 403 Forbidden');
    exit("Access Denied.");
}

$part = isset($_GET['part']) ? (int)$_GET['part'] : 0;
if ($part < 1) exit("Invalid Part Number.");

function getFileList($dir) {
    $files = [];
    $it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
    foreach (new RecursiveIteratorIterator($it) as $file) {
        if ($file->isFile()) $files[] = $file->getRealPath();
    }
    sort($files); 
    return $files;
}

$allFiles = getFileList($SOURCE_DIR);
$totalFiles = count($allFiles);
$start = ($part - 1) * $FILES_PER_PART;

if ($start >= $totalFiles) {
    header('HTTP/1.1 404 Not Found');
    exit("No more parts.");
}

$currentBatch = array_slice($allFiles, $start, $FILES_PER_PART);


$zipName = $TEMP_DIR . "part_{$part}.zip";
$zip = new ZipArchive();

if ($zip->open($zipName, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
    foreach ($currentBatch as $file) {
        $relativePath = ltrim(str_replace($SOURCE_DIR, '', $file), DIRECTORY_SEPARATOR);
        $zip->addFile($file, $relativePath);
    }
    $zip->close();
}


header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="part_' . $part . '.zip"');
header('Content-Length: ' . filesize($zipName));

readfile($zipName);


unlink($zipName);
exit;