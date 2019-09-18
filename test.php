<?php
require_once __DIR__ . '/vendor/autoload.php';
use yuanmaketang\Hello\helloComposer;
$helloComposer = new helloComposer();
echo $helloComposer->sayHello();