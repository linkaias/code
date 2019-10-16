<?php
namespace lkcodes\Mycode\lib;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Class Excel
 * @package lkcodes\Mycode\lib
 * by lk
 */
class Excel{

    public function __construct()
    {
        set_time_limit(0);
    }

    /**
     * 导出表格
     * @param $data
     * @param $params
     *          $param=[
                    'name'=>'风险广告列表',
                    'suffix'=>'xlsl',
                    'top_title'=>['ID','姓名','age'],
                    'title'=>'title',
                    'data_title'=>['test1','test2','test3'],
                    'description'=>'文件描述',
                    'keywords'=>'关键词',
                    'category'=>'分类',
                    ];
     * @return string
     */
    public function exportData($data,$params){
        $param=[
            'name'=>isset($params['name'])?$params['name']:'测试',
            'suffix'=>isset($params['suffix'])?$params['suffix']:'xlsl',
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
            $num = Tool::chrNext($num);
        }

        //数据写入
        $a=1;
        foreach ($data as $k) {
            $a++;
            $num ='A';
            foreach($param['data_title'] as $title){
                try {
                    $spreadsheet->getActiveSheet()->setCellValue($num.$a, $k[$title]);
                    //$spreadsheet->getActiveSheet()->getStyle($num.$a)->getNumberFormat()
                        //->setFormatCode(DataType::TYPE_STRING2);
                } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {

                }
                $num = Tool::chrNext($num);
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
     * 根据类型导出文件
     * @param $name
     * @param $type
     * @param $obj
     * @throws Exception
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
                $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($name.'.xls', 'xls');
                $writer->save('php://output');
                break;
        }
    }


}