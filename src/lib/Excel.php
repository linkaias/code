<?php
namespace lkcodes\Mycode\lib;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Class Excel
 * @package lkcodes\Mycode\lib
 * by lk
 */
class Excel extends ParentController{

    public function __construct()
    {
        parent::__construct();
        set_time_limit(0);
    }

    /**
     * 导出表格
     * @param $data
     * @param $params
     *          $param=[
     *            'name'=>'风险广告列表',
     *            'suffix'=>'xlsx',
     *            'top_title'=>['ID','姓名','age'],
     *            'title'=>'title',
     *            'data_title'=>['test1','test2','test3'],
     *            'description'=>'文件描述',
     *            'keywords'=>'关键词',
     *            'category'=>'分类',
     *          ];
     * @return string
     * @throws \Exception
     */
    public function exportData($data,$params){
        $param=[
            'name'=>isset($params['name'])?$params['name']:'测试',
            'suffix'=>isset($params['suffix'])?$params['suffix']:'xlsx',
            'top_title'=>isset($params['top_title'])?$params['top_title']:[],
            'title'=>isset($params['title'])?$params['title']:'title',
            'data_title'=>isset($params['data_title'])?$params['data_title']:[],
            'description'=>isset($params['description'])?$params['description']:'文件描述',
            'keywords'=>isset($params['keywords'])?$params['keywords']:'关键词',
            'category'=>isset($params['category'])?$params['category']:'分类',
        ];
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator("lk")//设置作者
            ->setLastModifiedBy("lk")//最后修改
            ->setTitle($param['title'])//设置标题
            ->setDescription($param['description'])//设置文件描述
            ->setKeywords($param['keywords'])//关键词
            ->setCategory($param['category']);

        //规格设定
        $num ='A';
        foreach ($param['top_title'] as $title){
            $_num = $num.'1';
            try {
                //设置首行标题
                $spreadsheet->getActiveSheet()->setCellValue($_num, $title);
                //自动设置列宽度
                $spreadsheet->getActiveSheet()->getColumnDimension($num)->setAutoSize(true);
                //设置自动换行'\n'
                $spreadsheet->getActiveSheet()->getStyle($_num)->getAlignment()->setWrapText(true);

            } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
            }
            $num = chrNext($num);
        }

        //数据写入
        $a=1;
        foreach ($data as $k) {
            $a++;
            $num ='A';
            foreach($param['data_title'] as $title){
                try {
                    //判断字段是否为图片
                    $is_img = substr($title,0,5);
                    //图片
                    if($is_img =="_IMG_"){
                        $objDrawing[$num.$a] = new Drawing();
                        $objDrawing[$num.$a]->setPath($k[$title]);
                        // 设置宽度高度
                        $objDrawing[$num.$a]->setHeight(60);//照片高度
                        //$objDrawing[$num.$a]->setWidth(30); //照片宽度

                        //设置图片所在的位置
                        $objDrawing[$num.$a]->setCoordinates($num.$a);

                        // 写入图片在指定格中的X坐标值
                        $objDrawing[$num.$a]->setOffsetX ( 12);
                        // 写入图片在指定格中的Y坐标值
                        $objDrawing[$num.$a]->setOffsetY ( 12 );
                        $objDrawing[$num.$a]->setWorksheet($spreadsheet->getActiveSheet());
                    }
                    //数据
                    else{
                        $spreadsheet->getActiveSheet()->setCellValue($num.$a, $k[$title]);
                    }
                    //$spreadsheet->getActiveSheet()->getStyle($num.$a)->getNumberFormat()
                    //->setFormatCode(DataType::TYPE_STRING2);
                } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {

                }
                $num = chrNext($num);
            }
        }

        try {
            $this->saveTableType($param['name'], $param['suffix'], $spreadsheet);
        } catch (Exception $e) {
            return $e->getMessage();
        }

        //合并单元格
        //$spreadsheet->getActiveSheet()->mergeCells('A2:A3');
        //$spreadsheet->getActiveSheet()->unmergeCells('A2:A3');

        //释放内存
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

    }


    /**
     * 表格读取导入
     * @param $file_path
     * @return array|bool
     */
    public function importTable($file_path)
    {
        try {
            $inputFileType = IOFactory::identify($file_path);
        } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
            return false;
        } //传入Excel路径
        try {
            $excelReader = IOFactory::createReader($inputFileType);
        } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
            return false;
        }
        if(isset($excelReader)){
            try {
                $PHPExcel = $excelReader->load($file_path);
            } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
                return false;
            } // 载入excel文件
            try {
                $sheet = $PHPExcel->getSheet(0);
            } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
                return false;
            } // 读取第一個工作表
            $data = $sheet->toArray();
            if($data){
                return $data;
            }else{
                return false;
            }
        }else{
            return false;
        }


    }


    /**
     * 根据类型导出文件
     * @param $name
     * @param $type
     * @param $obj
     * @throws Exception
     * @throws \Exception
     */
    protected function saveTableType($name,$type,$obj)
    {
        header('Cache-Control: max-age=0');//禁止缓存
        switch ($type){
            case 'xlsx':

                header('Cache-Control: max-age=0');
                header('Content-Disposition: attachment;filename="' . $name.'.xlsx' . '"');
                $writer = new Xlsx($obj);
                $writer->save('php://output');
                break;
            case 'csv':
                header('Cache-Control: max-age=0');
                header('Content-Disposition: attachment;filename="' . $name.'.csv' . '"');
                $writer = new Csv($obj);
                $writer->save('php://output');
                break;
            case 'xls':
                header('Cache-Control: max-age=0');
                header('Content-Disposition: attachment;filename="'.$name.'.xls'.'"');
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xls($obj);
                $writer->save('php://output');
                break;
        }
    }


}
