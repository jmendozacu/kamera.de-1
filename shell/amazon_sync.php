<?php

ini_set('display_errors', true);
error_reporting(E_ALL);

$pathData = explode('/', dirname(__FILE__));
$tmpDir = '/tmp/' . $pathData[count($pathData) - 2];
if (!is_dir($tmpDir)) mkdir($tmpDir);
$lockFile = $tmpDir . '/' . basename(__FILE__) . '.lock';

$fp = fopen($lockFile, "a");
if (!$fp || !flock($fp, LOCK_EX | LOCK_NB, $eWouldBlock) || $eWouldBlock) {
    echo "Failed to acquire lock!\n";
    exit;
}

try {

    require dirname(__FILE__) . '/../app/Mage.php';
    Mage::app('admin', 'store');

    $model = new Colibo_Amazonia_Model_Sync(false);
    $model->sync();

} catch (Exception $e) {
    print_r($e->getMessage());
}

fclose($fp);
unlink($lockFile);