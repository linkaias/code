<?php
require_once __DIR__ . '/vendor/autoload.php';
use lkcodes\Mycode\lib\DB;
use lkcodes\Mycode\lib\Tool;
die;


$db = new DB();
$res =$db->query('select * from f_accounts limit 10');
var_dump($res);