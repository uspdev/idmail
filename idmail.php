<?php
require 'vendor/autoload.php';
use Uspdev\Idmail\IDMail;
use Dotenv\Dotenv;

if (!isset($argv[2])) {
    die("uso: ".$argv[0]." {list, add, remove, default}
        list <nusp>
        add/remove <endereço> <arquivo emails>
        default <nusp>\n");
}

$dotenv = Dotenv::createUnsafeImmutable(__DIR__);
$dotenv->load();
$mode = $argv[1];
$nusp = $argv[2];

if ($mode == "list") {
    $emails = IDMail::find_lists($nusp);
    foreach ($emails as $email) {
        echo $email['email'].":".$email['name']."\n";
    }
}
elseif ($mode == "add" or $mode == "remove") {
    $list = $argv[2];
    $emails = file($argv[3], FILE_IGNORE_NEW_LINES);
    $idmail = new IDMail("members");
    echo "logado...\n";
    $json = json_decode($idmail->members($mode, $list, $emails));
    var_dump($json);
}
else {
    $email = IDMail::find_email($nusp);
    echo $email."\n";
}

?>
