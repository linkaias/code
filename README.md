#备注

#####代码引入
````
composer require lkcodes/mycode
````

#####代码初始化
````
//在项目公共加载位置添加如下代码
(new \lkcodes\Mycode\lib\ParentController());
````
运行项目会生成config.php文件 
/lkcodes/mycode/src/lib/config.php
如未生成会使用备用配置文件
/lkcodes/mycode/src/lib/config_bak.php
请检查文件权限以自动生成或手动复制备用配置文件
#####修改数据库配置文件
````
 array (
    'db_host' => '127.0.0.1',
    'db_name' => 'dbname',
    'db_user' => 'root',
    'db_pwd' => 'root',
    'db_port' => '3360',
  ),
````

#####查看助手方法
````
//使用此方法可打印出所有助手方法 
http:test.php?showhelper
````
#####使用助手方法
````
//友好的变量输出 
dump($arr);
````
#####使用kint代码调试器
````
//使用trace
//在config.php中配置开启（默认开启）
Kint\Kint::trace();
//打印变量
Kint\Kint::trace($array);
//若开启助手方法可使用
d($array);
~d($array);等
详情请参考kint官方文档
````
#####使用DB库操作数据库
````
//首先配置好config.php配置项
基本使用：
查询/执行
$res = (new \lkcodes\Mycode\lib\DB())->query($sql);

//查询/执行操作
http:test.php?query_q=你的查询语句
如：http:test.php?query_q=show databases;
````

#####使用集成phpoffice导出/导入表格
````
//导出表格 
$table = new \lkcodes\Mycode\lib\Excel();
$data=[
    ['test1'=>'value1','test2'=>'value2','test3'=>'value3']
];
$param=[
                 'name'=>'风险广告列表', //文件名称
                 'suffix'=>'xlsx',  //文件后缀
                 'top_title'=>['ID','姓名','age'], //首行标题
                 'title'=>'title',
                 'data_title'=>['test1','test2','test3'], 数组对应字段
                 'description'=>'文件描述',
                 'keywords'=>'关键词',
                 'category'=>'分类',
               ];
$table->exportData($data,$param);

//导入表格数据 return array;
$table = new \lkcodes\Mycode\lib\Excel();
$table->importTable('/1.xlsx');
````

 

