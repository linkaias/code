<?php
namespace lkcodes\Mycode\lib;


use lkcodes\Mycode\other\Markdown;

/**
 * Class Tool
 * @package lkcodes\Mycode\lib
 */
class Tool  extends ParentController {

    /**
     * 获取所有类方法名称
     * @return array
     */
    public static function CreateFunctionMsg()
    {
        $con = dirname(__FILE__);
        $filename = scandir($con);
        $conname = array();
        $file_ = strrpos(__FILE__,'\\');
        $file_ = substr(__FILE__,0,$file_);
        foreach($filename as $k=>$v){
            // 跳过两个特殊目录   continue跳出循环
            if($v=="." || $v==".."){continue;}

            if(substr($v,strpos($v,"."))=='.php'){
                $conname[] = $file_.'/'.$v;
            }
        }
        if($conname){
            $res =[];
            foreach ($conname as $file){
                $r =Markdown::getArray($file);
                $res[] = $r;
            }
            echo "<pre />";
            print_r($res);die;
        }


    }

    /**
     * 递归删除文件夹及文件夹下所有文件
     * @param $path
     * @return bool|null
     */
    public static function deldir($path)
    {
        //给定的目录不是一个文件夹
        if(!is_dir($path)){
            return null;
        }
        $fh = opendir($path);
        while(($row = readdir($fh)) !== false){
            //过滤掉虚拟目录
            if($row == '.' || $row == '..'){
                continue;
            }
            if(!is_dir($path.'/'.$row)){
                unlink($path.'/'.$row);
            }
            self::deldir($path.'/'.$row);
            //$this->deldir($path.'/'.$row);
        }
        //关闭目录句柄，否则出Permission denied
        closedir($fh);
        //删除文件之后再删除自身
        if(!rmdir($path)){
            echo $path.'无权限删除<br>';
        }
        return true;
    }


    /**
     * 英文字母递增
     * @param $a
     * @return string
     */
    public static function chrNext(&$a)
    {
        $strList = preg_split("//u", $a, -1, PREG_SPLIT_NO_EMPTY);
        if ($strList[count($strList) - 1] == 'Z') {
            $str = '';
            foreach ($strList as $key => $value) {
                if ($key != count($strList) - 1)
                    $str .= $value;
            }
            if ($str == '') {
                $str = chr(ord('A') - 1);
            }
            $str = Tool::chrNext($str) . 'A';
        } else {
            $strList[count($strList) - 1] = chr(ord($strList[count($strList) - 1]) + 1);
            $str = implode('', $strList);
        }
        $a = $str;
        return $a;
    }

    /**
     * 根据时区名称获取当前时区时间
     * @param $timezone
     * @param string $format
     * @return false|string
     */
    public  static function getTimeZone($timezone,$format = "Y-m-d H:i:s")
    {
        $timezone_out = date_default_timezone_get();
        date_default_timezone_set($timezone);
        $Time = date($format);
        date_default_timezone_set($timezone_out);
        return $Time;
    }

}