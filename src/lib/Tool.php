<?php
namespace lkcodes\Mycode\lib;
class Tool {

    /**
     * 递归删除文件夹及文件夹下所有文件
     * @param $path
     * @return bool|null
     */
    public static function deldir($path){
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
        deldir($path.'/'.$row);
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
     * 导出表格  需要先引入phpExcel类
     * @param $data
     * @param $param
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Reader_Exception
     * @throws PHPExcel_Writer_Exception
     */
    public static function exportExcel($data,$param){
        //传入数据示例
        /*$param['colwidth'] = [ 50,30,30,30,30,30,30];
        $param['center'] = [ true,true,true,true,true,true,true];
        $param['top_title'] = [ '公司名称','广告账户','账户ID','开户天数','时区','开户日期','风险等级'];
        $param['data_title'] = [ 'chinese_legal_entity_name','account_name','account_id','age','timezone_name','created_time','level'];
        $param['title'] ='新开无消耗账户列表';
        exportExcel($info,$param);*/
        set_time_limit(0);
        ini_set('memory_limit','1024M');
        //导出表格
        include_once $_SERVER['DOCUMENT_ROOT']."/Phpexcel18/PHPExcel.php";
        $objPHPExcel  = new \PHPExcel();
        //设置文件的一些属性，在xls文件——>属性——>详细信息里可以看到这些值
        $objPHPExcel->getProperties()->setCreator("ctos")->setLastModifiedBy("ctos")
            ->setTitle("Office 2007 XLSX Test Document")
            ->setSubject("Office 2007 XLSX Test Document")
            ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
            ->setKeywords("office 2007 openxml php")
            ->setCategory("Result file");
        //设置列宽
        $num ='A';
        foreach ($param['colwidth'] as $colwidth){
            $objPHPExcel->getActiveSheet()->getColumnDimension($num)->setWidth($colwidth);
            $num = chrNext($num);
        }

        //设置水平居中显示
        $num ='A';
        foreach ($param['center'] as $center){
            if($center){
                $objPHPExcel->getActiveSheet()->getStyle($num)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            }
            $num = chrNext($num);
        }
        //设置头标题
        $num ='A';
        foreach ($param['top_title'] as $top_title){
            $_num = $num.'1';
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue($_num, $top_title);
            $num = chrNext($num);

        }

        //设置默认值
        $a = 1;
        foreach($data as $k){
            $a++;
            $num ='A';
            foreach ($param['data_title'] as $data_title){

                //设置数字的科学计数法显示为文本
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValueExplicit($num.$a, $k[$data_title],\PHPExcel_Cell_DataType::TYPE_STRING2);
                $num = chrNext($num);
            }
            //设置自动换行
            $num ='A';
            foreach ($param['data_title'] as $n){
                $objPHPExcel->getActiveSheet()->getStyle($num."$a")->getAlignment()->setWrapText(true);
                $num = chrNext($num);
            }
        }
        $objPHPExcel->getActiveSheet()->setTitle($param['title']);
        $objPHPExcel->setActiveSheetIndex(0);
        // excel头参数
        header('Content-Type: application/vnd.ms-excel');
        $_title=$param['title'].date('YmdHis');
        header('Content-Disposition: attachment;filename="'.$_title.'.xls"');                            //日期为文件名后缀
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');                                                        //excel5为xls格式，excel2007为xlsx格式
        $objWriter->save('php://output');
        die;


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