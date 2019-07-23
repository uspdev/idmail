<?php
require 'vendor/autoload.php';
require 'IDMail.php';
use Dotenv\Dotenv;

if (!isset($argv[2])) {
    die("uso: ".$argv[0]." [nusp]\n");
}

$dotenv = Dotenv::create(__DIR__);
$dotenv->load();
$mode = $argv[1];
$nusp = $argv[2];

if ($mode == "list") {
    $cache = IDMail::cache_get_emails($nusp);
    if ($cache) {
        $json = json_decode($cache);
    }
    else {
        $idmail = new IDMail("list");
        $json = json_decode($idmail->id_get_emails($nusp));
    }
    $emails = IDMail::list_emails($json, "ime.usp.br", ["Institucional", "Grupo"]);
    foreach ($emails as $email) {
        echo $email['email'].":".$email['name']."\n";
    }
}
else {
    $email = IDMail::cache_find_email($nusp, ["P", "O"]);
    if ($email == "") {
        $idmail = new IDMail("all");
        $json = json_decode($idmail->id_get_emails($nusp));
        $email = IDMail::extract_email($json, "ime.usp.br", ["Pessoal", "Secundaria"]);
    }
    echo $email."\n";
}


?>
