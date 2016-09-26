<?php

ini_set('display_errors', true);
error_reporting(E_ALL);

/** Init */
require_once(dirname(__FILE__) . '/../vendor/autoload.php');
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Formatter\OutputFormatter;

/** Output Formatter */
$output = new ConsoleOutput();
$output->setFormatter(new OutputFormatter(true));
$output->writeln('<question>I will obey ...</question>');

try {

    /** Lock File */
    $pathData = explode('/', dirname(__FILE__));
    $tmpDir = '/tmp/' . $pathData[count($pathData) - 2];
    if (!is_dir($tmpDir)) mkdir($tmpDir);
    $lockFile = $tmpDir . '/' . basename(__FILE__) . '.lock';

    $fp = fopen($lockFile, "a");
    if (!$fp || !flock($fp, LOCK_EX | LOCK_NB, $eWouldBlock) || $eWouldBlock) {
        throw new \Exception('Failed to acquire lock!');
    }

    /** Mage App */
    require dirname(__FILE__) . '/../app/Mage.php';
    Mage::app('admin', 'store');

    /** Import */
    $model = new Colibo_Amazonia_Model_Import($output);
    $model->import();

    fclose($fp);
    unlink($lockFile);

} catch (\Exception $e) {
    $output->writeln('<error>' . $e->getMessage() . '</error>');
}

$output->writeln('<question>Done.</question>');