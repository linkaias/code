<?php

use lkcodes\Mycode\lib\DB;
use lkcodes\Mycode\lib\ParentController;
use function lkcodes\Mycode\lib\dump;


//require './src/lib/kint.phar';
require_once __DIR__ . '/vendor/autoload.php';
(new \lkcodes\Mycode\lib\ParentController());

echo 1;
die;

//Kint::dump($GLOBALS, $_SERVER); // pass any number of parameters
//d($GLOBALS, $_SERVER); // or simply use d() as a shorthand
//Kint::trace();
//d(time());thinkphp
//Kint::trace(); // Debug backtrace
//d(1); // Debug backtrace shorthand

//s($GLOBALS); // Basic output mode

//~d($GLOBALS); // Text only output mode

//Kint::$enabled_mode = false; // Disable kint
//d('Get off my lawn!'); // Debugs no longer have any effect

die;
$res =new DB();

//$res =$res->query('select * from config');
//var_dump($res );
die;
