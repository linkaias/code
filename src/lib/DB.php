<?php
namespace lkcodes\Mycode\lib;
use lkcodes\Mycode\lib\DB\Mpdo;

class DB extends Mpdo
{
    protected $connection;
    function __construct()
    {
        /*$xml = str_replace('\\','/','vendor/config.xml');
        $data = simplexml_load_file($xml,'SimpleXMLElement',LIBXML_NOCDATA);
        $conn = (array) $data->global->connection;*/

        //parent::__construct($conn['host'],$conn['username'],$conn['password'],$conn['dbname']);
        parent::__construct('127.0.0.1','root','root','fbapi');
    }
}