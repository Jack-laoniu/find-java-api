<?php
/**
 * Created by PhpStorm.
 * User: bluefish
 * Date: 2019/8/15
 * Time: 下午5:58
 */

namespace SelfTools\FindJavaApi;
use Illuminate\Support\Arr;


class FindLibrary {
    private $class = array();
    const COMPOSER_REGEX = '/^([A-Za-z]+\\\\)+Intf|Logics(\\\\[A-Za-z]+)+$/';
    public function __construct()
    {
        if(empty($this->class)){
            $this->class = PublicCode::searchCode(require base_path('vendor/composer') . '/autoload_classmap.php', self::COMPOSER_REGEX, 'key', true);
        }
    }
    public function handle($code, $path_func)
    {
        $library_result = PublicCode::recursiveGetLibrary($code,$this->class);
        $result = array_merge($library_result,$GLOBALS['first_pipline']);
        if(count($result) == 0){
            dd('php no need java');
        }
        $GLOBALS['pipline'] = call_user_func_array('array_merge_recursive',$result);
        return $path_func($GLOBALS['pipline']);
    }
}