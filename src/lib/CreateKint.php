<?php
namespace lkcodes\Mycode\lib;
/**
 * Class Kint
 * @package lkcodes\Mycode\lib
 */
class CreateKint extends ParentController {


    /**
     * CreateKint constructor.
     * @param bool $use_hepler
     */
    public function __construct($use_hepler=true)
    {
        //常用示例
        /*
         Kint::dump($GLOBALS, $_SERVER); // pass any number of parameters
         d($GLOBALS, $_SERVER);

        //Kint::trace(); // Debug backtrace
        //d(1); // Debug backtrace shorthand

        //s($GLOBALS); // Basic output mode

        //~d($GLOBALS); // Text only output mode

        //Kint::$enabled_mode = false; // Disable kint
        //d('Get off my lawn!'); // Debugs no longer have any effect
        */
        //是否禁用助手功能 d();等
        if(!$use_hepler)define('KINT_SKIP_HELPERS', true);

        require_once __DIR__."/kint.phar";
    }





}