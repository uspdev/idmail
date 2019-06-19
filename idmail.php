<?php
require 'vendor/autoload.php';
require 'IDMail.php';
use Dotenv\Dotenv;

if (!isset($argv[1])) {
    die("uso: ".$argv[0]." [nusp]\n");
}

$dotenv = Dotenv::create(__DIR__);
$dotenv->load();

$idmail = new IDMail();

$json = json_decode($idmail->id_get_emails($argv[1]));
echo $idmail->extract_email($json)."\n";

?>
