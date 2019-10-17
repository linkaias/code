<?php

use lkcodes\Mycode\lib\DB;
use lkcodes\Mycode\lib\Tool;
use lkcodes\Mycode\other\DocParserFactory;
use lkcodes\Mycode\other\WebInfo;
//require './src/lib/kint.phar';
require_once __DIR__ . '/vendor/autoload.php';
//Kint::dump($GLOBALS, $_SERVER); // pass any number of parameters
d($GLOBALS, $_SERVER); // or simply use d() as a shorthand
d(time());
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
