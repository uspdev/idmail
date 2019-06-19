<?php
require 'vendor/autoload.php';
require 'IDMail.php';

if (!isset($argv[1])) {
    die("uso: ".$argv[0]." [nusp]\n");
}

$idmail = new IDMail();

$json = json_decode($idmail->id_get_emails($argv[1]));
echo $idmail->extract_email($json)."\n";

?>
