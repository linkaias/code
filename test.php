<?php
require_once __DIR__ . '/vendor/autoload.php';

use Laurentvw\Scrapher\Scrapher;
use Laurentvw\Scrapher\Selectors\RegexSelector;
use lkcodes\Mycode\lib\DB;
use lkcodes\Mycode\lib\Excel;
use lkcodes\Mycode\lib\Tool;

$data=[
    ['test1'=>'test1111','test2'=>'12121212121212121','test3'=>'test333'],
    ['test1'=>'test1111','test2'=>'test222','test3'=>'test333'],
    ['test1'=>'test1111','test2'=>'test222','test3'=>'test333'],
];
$param=[
    'name'=>'风险广告列表',
    'suffix'=>'xls',
    'top_title'=>['T1','T2','T3'],
    'title'=>'title',
    'data_title'=>['test1','test2','test3'],
    'description'=>'文件描述',
    'keywords'=>'关键词',
    'category'=>'分类',
];
$a = (new Excel())->exportData($data,$param);


die;


$url = 'https://cn.vuejs.org/v2/guide/';

//$res = QueryList::get('https://www.layui.com/doc/element/table.html')->find('cite')->getString();
$res = QueryList::get('https://blog.csdn.net/my_yang/article/details/43882661')
    ->find('#asideArchive')->children('.aside-content')->children('ul')->children('li')
    ->find('a')
    ->getString();
var_dump($res);die;

/*bin/magento setup:install --base-url=http://mg29.cn --db-host=localhost --db-name=mg29 --db-user=root --db-password=root --admin-firstname=admin --admin-lastname=admin --admin-email=admin@admin.com --admin-user=admin --admin-password=123456abc --language=en_US --currency=USD --timezone=America/Chicago --use-rewrites=1*/


$ql->find('#head')->append('<div>Append content</div>')->find('div')->htmls();
$ql->find('.two')->children('img')->attrs('alt'); // Get the class is the "two" element under all img child nodes
// Loop class is the "two" element under all child nodes
$data = $ql->find('.two')->children()->map(function ($item){
    // Use "is" to determine the node type
    if($item->is('a')){
        return $item->text();
    }elseif($item->is('img'))
    {
        return $item->alt;
    }
});



$pattern = '/http:\/\/.*com/';//需要转义/

preg_match($pattern,$url,$match);

var_dump($match);

$str = 'http://baidu.com';

$pattern = '#http://.*com#';//不需要转义/

preg_match($pattern,$str,$match);

var_dump( $match);
die;

$scrapher = new Scrapher($url);

// Match all links on a page
$regex = '/<a.*?href=(?:"(.*?)").*?>(.*?)<\/a>/ms';

$matchConfig = array(
    array(
        'name' => 'url',
        'id' => 1, // the first match (.*?) from the regex
    ),
    array(
        'name' => 'title',
        'id' => 2, // the second match (.*?) from the regex
    ),
);


$matches = $scrapher->with(new RegexSelector($regex, $matchConfig));

$results = $matches->get();
print_r($results);
die;



$data = QueryList::get('https://cn.vuejs.org/v2/guide/');


$res =QueryList::get('https://shimo.im/sheet/4wu1FFUVsc8PxJ2h/RIDOC')
    ->rules([
        'title'=>array('span','text'),
        'link'=>array('h3>a','href')
    ])
    ->query()
    ->getData();
print_r($res);die;

$data = QueryList::get('https://cn.vuejs.org/v2/guide/')
    // 设置采集规则
    ->rules([
        'title'=>array('h3','text'),
        'link'=>array('h3>a','href')
    ])
    ->query()->getData();


print_r($data->all());




die;


$db = new DB();
$res =$db->query('select * from f_accounts limit 10');
var_dump($res);