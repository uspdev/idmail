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
    $emails = IDMail::find_lists($nusp);
    foreach ($emails as $email) {
        echo $email['email'].":".$email['name']."\n";
    }
}
else {
    $email = IDMail::find_email($nusp);
    echo $email."\n";
}

?>
