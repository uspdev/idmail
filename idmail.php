<?php
require 'vendor/autoload.php';
require 'IDMail.php';
use Dotenv\Dotenv;

if (!isset($argv[1])) {
    die("uso: ".$argv[0]." [nusp]\n");
}

$dotenv = Dotenv::create(__DIR__);
$dotenv->load();
$nusp = $argv[1];

$email = IDMail::find_mail($nusp, ["P", "O"]);
if ($email == "") {
    $idmail = new IDMail();
    $json = json_decode($idmail->id_get_emails($nusp));
    $email = $idmail->extract_email($json, "ime.usp.br", ["Pessoal", "Secundaria"]);
}

echo $email."\n";

?>
