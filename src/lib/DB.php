<?php
namespace lkcodes\Mycode\lib;

class DB extends Mpdo
{
    protected $connection;

    function __construct()
    {
        $_file =dirname(__FILE__);
        $file =$_file."/config.php";

        if(file_exists($file)){
            try{
                $config = include "$file";
                parent::__construct($config['db_config']['db_host'],$config['db_config']['db_user'],$config['db_config']['db_pwd'],$config['db_config']['db_name']);
            }catch (\Exception $e){}
            catch (\Error $e){}

        }else{
            echo "配置文件获取异常!";
            exit();
        }


        //parent::__construct($conn['host'],$conn['username'],$conn['password'],$conn['dbname']);

    }

}