<?php
namespace lkcodes\Mycode\lib;


use lkcodes\Mycode\other\Markdown;

/**
 * Class ParentController
 * @package lkcodes\Mycode\lib
 */
class ParentController {

    public function __construct()
    {
        header("Content-type: text/html; charset=utf-8");
        $this->setConfig();
        $this->setKint();
        $this->helper();
        $this->dbHelper();
    }

    /**
     * create Config Files
     */
    protected function setConfig()
    {
        $_file =dirname(__FILE__);
        $file =$_file."/config.php";
        if(!file_exists($file)){
            $data=$obj =[
                /* 数据库配置 */
                'db_config'=>[
                    //'db_type' => 'mysql', // 数据库类型,
                    'db_host' => '127.0.0.1', // 服务器地址
                    'db_name' => 'dbname', // 数据库名
                    'db_user' => 'root', // 用户名
                    'db_pwd'  => 'root', // 用户名
                    'db_port' => '3360', // 用户名
                ],
                'kint_config'=>[
                    'open' => true,
                    'open_helper' => false,
                ]
            ];
            try{
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
            //配置文件获取失败请检查文件权限或手动创建
            catch (\Exception $e){
                /*echo '配置文件获取失败请检查文件夹权限（vendor）或手动创建配置文件.'."<pre />";
                echo '路径：vendor/lkcodes/mycode/src/config.php';
                exit();*/
            }
        }



    }

    /**
     * config kint
     */
    protected function setKint()
    {
        $_file =dirname(__FILE__);
        $file =$_file."/config.php";
        $file_bak = $_file."/config_bak.php";
        if(file_exists($file)){
            $config = include_once "$file";
            if($config['kint_config']['open']){
                (new CreateKint($config['kint_config']['open_helper']));
            }
        }
        //使用备份配置文件
        elseif(file_exists($file_bak)){
            $config = include_once "$file_bak";
            if($config['kint_config']['open']){
                (new CreateKint($config['kint_config']['open_helper']));
            }
        }
        else{
            echo '配置文件获取失败请检查文件权限或手动创建'."<pre />";
            echo '路径：vendor/lkcodes/mycode/src/config.php';
            exit();
        }
    }

    /**
     * 获取所有助手方法名称
     */
    public function helper()
    {
        if(isset($_GET['showhelper'])){
            $con = dirname(__FILE__);
            $file_path = $con.'/helper.php';
            $file_handle = fopen($file_path, "r");

            $arr = array();
            while (!feof($file_handle)) {
                $line = fgets($file_handle);
                $t=[];
                if(trimx($line) =='/**'){
                    array_push($t,trimx(fgets($file_handle)));
                }
                if(trimx($line)=='*/'){
                    $temp=trimx(fgets($file_handle));
                    if(stristr($temp,'function')){
                        array_push($t,substr($temp,8));
                    }
                }
                if($t)$arr[] = $t;

            }
            fclose($file_handle);
            dump($arr);
        }

    }

    /**
     * 数据库操作助手
     */
    public function dbHelper()
    {
        //查询sql
        if(isset($_GET['query_q'])){
            $query = trim($_GET['query_q']);
            if($query){
                try {
                    $res = (new DB())->query($query);
                    dump(obj_to_arr($res));
                } catch (\Exception $e) {
                    echo '请检查配置文件是否正确';
                    dump($e->getMessage());die;
                }
            }
            return;
        }
        //切换数据库可视化查询
        if(isset($_GET['query_t'])){
            $query_t_q = isset($_GET['query_t_q'])?($_GET['query_t_q']):'';
            $query_t_d = isset($_GET['query_t_d'])?($_GET['query_t_d']):'';
            try{
                $databases = (new DB())->query("show databases");
            }catch (\Exception $e){
                echo '请检查配置文件是否正确';
                dump($e->getMessage());die;
            }catch (\Error $e){
                echo '请检查配置文件是否正确';
                dump($e->getMessage());die;
            }
            $select='<select id="my_query_select" style="height: 40px !important;width: 300px !important;padding: 5px !important">';
            foreach ($databases->rows as $database){
                $selected ='';
                if($database['Database'] == $query_t_d)$selected='selected="selected"';
                $select.='<option '.$selected.' value="'.$database['Database'].'">'.$database['Database'].'</option>';
            }
            $select.='</select>';
            echo $select.'<input id="my_query_input" style="height: 30px !important ;width: 300px!important;padding: 5px !important" name="query" value="'.$query_t_q.'"><button id="my_query_btn" style="height: 42px !important;border: none !important;color: white !important;background: gray !important;width: 50px !important;">执 行</button><script>var btn =document.getElementById("my_query_btn"); btn.onclick=function(){ var href=window.location.href;var sql = document.getElementById("my_query_input").value;  var select = document.getElementById("my_query_select");var index=select.selectedIndex ;select=select.options[index].value;   href=href+"&query_t=1&query_t_q="+sql+"&query_t_d="+select; window.location.href=href };   </script>';

            $mysql = switchDatabaseDb($query_t_d);
            dump($query_t_q);
            if($query_t_q){
                try {
                    $res = $mysql->query($query_t_q);
                    dump(obj_to_arr($res));
                } catch (\Exception $e) {
                }
            }
            return;
        }
    }
}
