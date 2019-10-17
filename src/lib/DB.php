<?php
namespace lkcodes\Mycode\lib;
use lkcodes\Mycode\lib\DB\Mpdo;

class DB extends Mpdo
{
    protected $connection;

    function __construct()
    {
        (new ParentController());
        $_file = strrpos(__FILE__,'\\');
        $_file = substr(__FILE__,0,$_file);
        $file =$_file."/config.php";

        if(file_exists($file)){
            $config = include_once "$file";
            parent::__construct($config['db_config']['db_host'],$config['db_config']['db_user'],$config['db_config']['db_pwd'],$config['db_config']['db_name']);
        }else{
            echo "正在生产配置文件,请刷新页面!";
            exit();
        }
        //parent::__construct($conn['host'],$conn['username'],$conn['password'],$conn['dbname']);

    }

}