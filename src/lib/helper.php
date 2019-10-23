<?php
/**
 * 助手方法
 */


if (!function_exists('dump')) {
    /**
     * 浏览器友好的变量输出
     * @param mixed $vars 要输出的变量
     * @return void
     */
    function dump(...$vars)
    {
        ob_start();
        var_dump(...$vars);

        $output = ob_get_clean();
        $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);

        if (PHP_SAPI == 'cli') {
            $output = PHP_EOL . $output . PHP_EOL;
        } else {
            if (!extension_loaded('xdebug')) {
                $output = htmlspecialchars($output, ENT_SUBSTITUTE);
            }
            $output = '<pre>' . $output . '</pre>';
        }

        echo $output;
    }
}

if (!function_exists('timeline')) {
    /**
     * 返回格式化后的时间
     * @param $time 时间戳
     * @return false|string
     */
    function timeline($time){
        if(time()<=$time){
            return date("Y-m-d H:i:s",$time);
        }else{
            $t = time()-$time;
            $f = array(
                '31536000'=>'年',
                '2592000'=>'个月',
                '604800'=>'星期',
                '86400'=>'天',
                '3600'=>'小时',
                '60'=>'分钟',
                '1'=>'秒'
            );
            foreach($f as $k=>$v){
                if(0 != $c = floor($t/(int)$k)){
                    return $c.$v.'前';
                }
            }
        }
    }
}

if (!function_exists('file_ext')) {
    /**
     * 获取文件拓展名
     * @param $file 文件名称
     * @return string
     */
    function file_ext($file)
    {
        return strtolower(pathinfo($file, 4));
    }
}

if (!function_exists('del_dir')) {
    /**
     * 递归删除目录
     * @param $path 路径
     * @return bool|null
     */
    function del_dir($path)
    {
        //给定的目录不是一个文件夹
        if (!is_dir($path)) {
            return null;
        }
        $fh = opendir($path);
        while (($row = readdir($fh)) !== false) {
            //过滤掉虚拟目录
            if ($row == '.' || $row == '..') {
                continue;
            }
            if (!is_dir($path . '/' . $row)) {
                unlink($path . '/' . $row);
            }
            del_dir($path . '/' . $row);
            //$this->deldir($path.'/'.$row);
        }
        //关闭目录句柄，否则出Permission denied
        closedir($fh);
        //删除文件之后再删除自身
        if (!rmdir($path)) {
            echo $path . '无权限删除<br>';
        }
        return true;
    }
}

if (!function_exists('chrNext')) {
    /**
     * 根据传入字母获取递增字母 如 A => B
     * @param $a
     * @return string
     */
    function chrNext(&$a)
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
            $str = chrNext($str) . 'A';
        } else {
            $strList[count($strList) - 1] = chr(ord($strList[count($strList) - 1]) + 1);
            $str = implode('', $strList);
        }
        $a = $str;
        return $a;
    }
}

if (!function_exists('getTimeZone')) {
    /**
     * 根据时区名称获取当前时区时间
     * @param $timezone
     * @param string $format
     * @return false|string
     */
    function getTimeZone($timezone, $format = "Y-m-d H:i:s")
    {
        $timezone_out = date_default_timezone_get();
        date_default_timezone_set($timezone);
        $Time = date($format);
        date_default_timezone_set($timezone_out);
        return $Time;
    }
}

if (!function_exists('insertContent')) {
    /**
     * 添加内容到文件指定行
     * @param $file_path
     * @param $content
     * @param int $iLine
     * @return bool
     */
    function insertContent($file_path, $content, $iLine = 1)
    {
        try {
            $file_handle = fopen($file_path, "r");
            $i = 0;
            $arr = array();
            while (!feof($file_handle)) {
                $line = fgets($file_handle);
                ++$i;
                if ($i == $iLine) {
                    $arr[] = $line . $content . "\n";
                } else {
                    $arr[] = $line;
                }
            }
            fclose($file_handle);
            unlink($file_path);
            foreach ($arr as $value) {
                file_put_contents($file_path, $value, FILE_APPEND);
            }
            return true;
        } catch (\Error $e) {
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
}

if (!function_exists('readFileContent')) {
    /**
     * 读取文件内容
     * @param $file
     * @return bool|mixed
     */
    function readFileContent($file)
    {
        if (file_exists($file)) {
            $fp = fopen($file, "r");
            $str = fread($fp, filesize($file));
            $content = str_replace("\r\n", "<br />", $str);
            fclose($fp);
            return $content;
        } else {
            return false;
        }

    }
}

if (!function_exists('hide_tel')) {
    /**
     * 隐藏手机号码中间四位
     * @param $phone
     * @return string|string[]|null
     */
    function hide_tel($phone){
        $IsWhat = preg_match('/(0[0-9]{2,3}[-]?[2-9][0-9]{6,7}[-]?[0-9]?)/i',$phone);
        if($IsWhat == 1){
            return preg_replace('/(0[0-9]{2,3}[-]?[2-9])[0-9]{3,4}([0-9]{3}[-]?[0-9]?)/i','$1****$2',$phone);
        }else{
            return  preg_replace('/(1[3587]{1}[0-9])[0-9]{4}([0-9]{4})/i','$1****$2',$phone);
        }
    }
}

if (!function_exists('trimx')) {
    /**
     * 去除字符串中换行空格
     * @param $str
     * @return string
     */
    function trimx($str)
    {
        $str = trim($str);
        $str = preg_replace("/\t/","",$str);
        $str = preg_replace("/\r\n/","",$str);
        $str = preg_replace("/\r/","",$str);
        $str = preg_replace("/\n/","",$str);
        $str = preg_replace("/ /","",$str);
        return trim($str); //返回字符串
    }
}

if (!function_exists('file_list')) {
    /**
     * 文件夹下文件列表 递归获取所有
     * @param string $folder
     * @param int $levels
     * @return array|bool
     */
    function file_list($folder = '', $levels =10 ) {
        if( empty($folder) )
            return false;

        if( ! $levels )
            return false;

        $files = array();
        if ( $dir = @opendir( $folder ) ) {
            while (($file = readdir( $dir ) ) !== false ) {
                if ( in_array($file, array('.', '..') ) )
                    continue;
                if ( is_dir( $folder . '/' . $file ) ) {
                    $files2 = file_list( $folder . '/' . $file, $levels -1);
                    if( $files2 )
                        $files = array_merge($files, $files2 );
                    else
                        $files[] = $folder . '/' . $file . '/';
                } else {
                    $files[] = $folder . '/' . $file;
                }
            }
        }
        @closedir( $dir );
        return $files;
    }
}

if (!function_exists('file_size')) {
    /**
     * 获取文件大小
     * @param $size
     * @param int $dec
     * @return string
     */
    function file_size($size, $dec=2) {
        $a = array("B", "KB", "MB", "GB", "TB", "PB");
        $pos = 0;
        while ($size >= 1024) {
            $size /= 1024;
            $pos++;
        }
        return round($size,$dec)." ".$a[$pos];
    }
}

if (!function_exists('get_client_ip')) {
    /**
     * 获取客户端IP地址
     * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
     * @param boolean $adv 是否进行高级模式获取（有可能被伪装）
     * @return mixed
     */
    function get_client_ip($type = 0,$adv=true) {
        $type       =  $type ? 1 : 0;
        static $ip  =   NULL;
        if ($ip !== NULL) return $ip[$type];
        if($adv){
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                $pos    =   array_search('unknown',$arr);
                if(false !== $pos) unset($arr[$pos]);
                $ip     =   trim($arr[0]);
            }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $ip     =   $_SERVER['HTTP_CLIENT_IP'];
            }elseif (isset($_SERVER['REMOTE_ADDR'])) {
                $ip     =   $_SERVER['REMOTE_ADDR'];
            }
        }elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip     =   $_SERVER['REMOTE_ADDR'];
        }
        // IP地址合法验证
        $long = sprintf("%u",ip2long($ip));
        $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
        return $ip[$type];
    }

}

if (!function_exists('str_auth_code')) {
    /**
     * 字符串编码/解码
     * @param $string
     * @param string $operation  默认解码 'DECODE'， 加密'ENCODE'
     * @param string $key 加密'key'
     * @param int $expiry 有效期/秒
     * @return bool|string
     */
    function str_auth_code($string, $operation = 'DECODE', $key = '', $expiry = 0) {
        $ckey_length = 4;
        $keya = md5(substr($key, 0, 16));
        $keyb = md5(substr($key, 16, 16));
        $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';
        $cryptkey = $keya.md5($keya.$keyc);
        $key_length = strlen($cryptkey);
        $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
        $string_length = strlen($string);
        $result = '';
        $box = range(0, 255);
        $rndkey = array();
        for($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }
        for($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }
        for($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }
        if($operation == 'DECODE') {
            if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
                return substr($result, 26);
            } else {
                return '';
            }
        } else {
            return $keyc.str_replace('=', '', base64_encode($result));
        }
    }
}

if (!function_exists('get_rmb')) {
    /**
     * 金额转大写格式
     * @param $num
     * @return string
     */
    function get_rmb($num){
        $c1 = "零壹贰叁肆伍陆柒捌玖";
        $c2 = "分角元拾佰仟万拾佰仟亿";
        $num = round($num, 2);
        $num = $num * 100;
        if (strlen($num) > 20) {
            return "金额过大";
        }
        $i = 0;
        $c = "";
        while (1) {
            if ($i == 0) {
                $n = substr($num, strlen($num)-1, 1);
            } else {
                $n = $num % 10;
            }
            $p1 = substr($c1, 3 * $n, 3);
            $p2 = substr($c2, 3 * $i, 3);
            if ($n != '0' || ($n == '0' && ($p2 == '亿' || $p2 == '万' || $p2 == '元'))) {
                $c = $p1 . $p2 . $c;
            } else {
                $c = $p1 . $c;
            }
            $i = $i + 1;
            $num = $num / 10;
            $num = (int)$num;
            if ($num == 0) {
                break;
            }
        }
        $j = 0;
        $slen = strlen($c);
        while ($j < $slen) {
            $m = substr($c, $j, 6);
            if ($m == '零元' || $m == '零万' || $m == '零亿' || $m == '零零') {
                $left = substr($c, 0, $j);
                $right = substr($c, $j + 3);
                $c = $left . $right;
                $j = $j-3;
                $slen = $slen-3;
            }
            $j = $j + 3;
        }

        if (substr($c, strlen($c)-3, 3) == '零') {
            $c = substr($c, 0, strlen($c)-3);
        }
        if (empty($c)) {
            return "零元整";
        }else{
            return $c . "整";
        }
    }
}

if (!function_exists('rand_color')) {
    /**
     * 生成随机颜色
     * @return string
     */
    function rand_color(){
        $char='abcdef0123456789';
        $str='';
        for($i=0;$i<6;$i++){
            $str .= substr($char,mt_rand(0,15),1);
        }
        return '#'.$str;
    }
}

if (!function_exists('sheng_xiao')) {
    /**
     * 根据年份获取生肖
     * @param $year
     * @return mixed
     */
    function sheng_xiao($year){
        $zodiac = array('猴','鸡','狗','猪','鼠','牛','虎','兔','龙','蛇','马','羊');
        return $zodiac[$year%12];
    }
}

if (!function_exists('get_constellation')) {
    /**
     * 获取星座
     * @param $month
     * @param $day
     * @return bool
     */
    function get_constellation($month, $day) {
        if ($month < 1 || $month > 12 || $day < 1 || $day > 31) return false;
        $constellations = array(
            array( "20" => "水瓶座"),
            array( "19" => "双鱼座"),
            array( "21" => "白羊座"),
            array( "20" => "金牛座"),
            array( "21" => "双子座"),
            array( "22" => "巨蟹座"),
            array( "23" => "狮子座"),
            array( "23" => "处女座"),
            array( "23" => "天秤座"),
            array( "24" => "天蝎座"),
            array( "22" => "射手座"),
            array( "22" => "摩羯座")
        );
        list($constellation_start, $constellation_name) = each($constellations[(int)$month-1]);
        if ($day < $constellation_start) list($constellation_start, $constellation_name) = each($constellations[($month -2 < 0) ? $month = 11: $month -= 2]);
        return $constellation_name;
    }
}

if (!function_exists('get_now_url')) {
    /**
     * 获取当前完整访问url
     * @return string
     */
    function get_now_url() {
        $url = 'http://';
        if (isset ( $_SERVER ['HTTPS'] ) && $_SERVER ['HTTPS'] == 'on') {
            $url = 'https://';
        }
        if ($_SERVER ['SERVER_PORT'] != '80') {
            $url .= $_SERVER ['HTTP_HOST'] . ':' . $_SERVER ['SERVER_PORT'] . $_SERVER ['REQUEST_URI'];
        } else {
            $url .= $_SERVER ['HTTP_HOST'] . $_SERVER ['REQUEST_URI'];
        }
        // 兼容后面的参数组装
        if (stripos ( $url, '?' ) === false) {
            $url .= '?t=' . time ();
        }
        return $url;
    }
}

if (!function_exists('get_memory')) {
    /**
     * 获取内存使用大小
     * @return string
     */
    function get_memory(){
        return round((memory_get_usage()/1024/1024),3)."M";
    }
}

if (!function_exists('cache_data')) {
    /**
     * 缓存文件
     * @param $key 缓存名称
     * @param string $value 为NULL时为删除
     * @param int $cachetime
     * @param $dir
     * @return bool|int|mixed
     */
    function cache_data($key,$value='',$cachetime=0,$dir='./var/cache'){

        $filename = $dir.$key.'.txt';
        if('' !== $value){//写入缓存
            if(is_null($value)){
                return @unlink($filename);
            }
            $dir = dirname($filename);
            if(!is_dir($dir)){
                mkdir($dir,0777);//创建目录
            }

            $cachetime = sprintf('%011d',$cachetime);
            return file_put_contents($filename,$cachetime.serialize($value));
        }

        if(!is_file($filename)){
            return 0;
        }else{
            $content = file_get_contents($filename);
            $cachetime = (int)substr($content,0,11);
            $value = substr($content,11);
            if(0 !=$cachetime && ($cachetime+fileatime($filename) <time())){
                @unlink($filename);
                return 0;
            }
            return unserialize($value);
        }
    }
}

if (!function_exists('count_down')) {
    /**
     * 倒计时
     * @param $settime
     * @return int|string
     */
    function count_down($settime)
    {
        $time = time();
        $settime  = strtotime($settime);
        $interval = $settime - $time;
        $days = $interval/(24*60*60);//精确到天数
        $days = intval($days);
        $hours = $interval /(60*60) - $days*24;//精确到小时
        $hours = intval($hours);
        $minutes = $interval /60 - $days*24*60 - $hours*60;//精确到分钟
        $minutes = intval($minutes);
        $seconds = $interval - $days*24*60*60 - $hours*60*60 - $minutes*60;//精确到秒
        $seconds = intval($seconds);
        $str = $days."天".$hours."小时".$minutes."分".$seconds."秒";
        if(intval($days)<0){
            $str=0;
        }
        return $str;
    }
}

if (!function_exists('obj_to_arr')) {
    /**
     * 对象转数组
     * @param $obj
     * @return array
     */
    function obj_to_arr($obj) {
        if (is_object($obj)) {
            $obj = get_object_vars($obj);
        }

        if (is_array($obj)) {
            return array_map(__FUNCTION__, $obj);
        }
        else {
            return $obj;
        }
    }
}

if (!function_exists('arr_to_obj')) {
    /**
     * 数组转对象
     * @param $d
     * @return object
     */
    function arr_to_obj($d) {
        if (is_array($d)) {

            return (object) array_map(__FUNCTION__, $d);
        }
        else {

            return $d;
        }
    }
}

if (!function_exists('arr_to_xml')) {
    /**
     * 数组转xml
     * @param $arr
     * @param int $level
     * @param string $ptagname
     * @return string|string[]|null
     */
    function arr_to_xml($arr, $level = 1, $ptagname = '') {
        $s = $level == 1 ? "<xml>" : '';
        foreach($arr as $tagname => $value) {
            if (is_numeric($tagname)) {
                $tagname = $value['TagName'];
                unset($value['TagName']);
            }
            if(!is_array($value)) {
                $s .= "<{$tagname}>".(!is_numeric($value) ? '<![CDATA[' : '').$value.(!is_numeric($value) ? ']]>' : '')."</{$tagname}>";
            } else {
                $s .= "<{$tagname}>".arr_to_xml($value, $level + 1)."</{$tagname}>";
            }
        }
        $s = preg_replace("/([\x01-\x08\x0b-\x0c\x0e-\x1f])+/", ' ', $s);
        return $level == 1 ? $s."</xml>" : $s;
    }
}

if (!function_exists('xml_to_arr')) {
    /**
     * xml转数组
     * @param $xml
     * @return array|mixed
     */
    function xml_to_arr($xml) {
        if (empty($xml)) {
            return array();
        }
        $result = array();
        $xmlobj = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        if($xmlobj instanceof SimpleXMLElement) {
            $result = json_decode(json_encode($xmlobj), true);
            if (is_array($result)) {
                return $result;
            } else {
                return array();
            }
        } else {
            return $result;
        }
    }
}

if (!function_exists('data_class')) {
    /**
     * 无限极分类
     * @param $arr
     * @param $id
     * @param $level
     * @return array
     */
    function data_class($arr,$id,$level)
    {
        $list =array();
        foreach ($arr as $k=>$v){
            if ($v['pid'] == $id){
                $v['level']=$level;
                $v['son'] = data_class($arr,$v['id'],$level+1);
                $list[] = $v;
            }
        }
        return $list;
    }
}

if(!function_exists('find_array')){
    /**
     * 查找二维数组是否存在某个元素
     * @param $target
     * @param $array
     * @return int
     */
    function find_array($target, $array)
    {
        if(!empty($array)){//数组不为空
            foreach($array as $k=>$v){//二维降为一维
                if(array_search($target,$v) !== false){
                    //array_searach(),在一维数组中搜索某个键值，存在返回对应的键名(有可能返回0)，不存在返回false，所以判断需要全等
                    return true;
                }
            }
        }
        return false;
    }
}