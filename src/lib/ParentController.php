<?php
namespace lkcodes\Mycode\lib;

use lkcodes\Mycode\lib\DB\Mysql;

/**
 * Class ParentController
 * @package lkcodes\Mycode\lib
 */
class ParentController {

    public function __construct()
    {
        header("Content-type: text/html; charset=utf-8");
        $this->setConfig();
    }

    /**
     * create Config Files
     */
    protected function setConfig()
    {
        $_file = strrpos(__FILE__,'\\');
        $_file = substr(__FILE__,0,$_file);
        $file =$_file."/config.php";
        $data=$obj =[
            /* 数据库配置 */
            'db_config'=>[
                //'db_type' => 'mysql', // 数据库类型,
                'db_host' => '127.0.0.1', // 服务器地址
                'db_name' => 'dbname', // 数据库名
                'db_user' => 'root', // 用户名
                'db_pwd'  => 'root', // 用户名
                'db_port' => '3360', // 用户名
            ]
        ];
        try{
            call_user_func([new Mysql($obj,$obj,$obj,$obj)]);
            if(!file_exists($file)){
                $o=fopen($file, "w");
                $txt = "<?php \n  return   ";
                fwrite($o, $txt);
                fclose($o);
                file_put_contents($file,var_export($data,true),FILE_APPEND );
                $o=fopen($file, "a");
                $txt = ";";
                fwrite($o, $txt);
                fclose($o);
            }
        }catch (\Exception $e){
            return '意外错误';
        }


    }

}