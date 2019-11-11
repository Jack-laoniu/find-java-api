<?php
/**
 * Created by PhpStorm.
 * User: bluefish
 * Date: 2019/8/15
 * Time: 下午6:00
 */

namespace SelfTools\FindJavaApi;


class FindPath {
    public function handle($code,$path_func)
    {
        $response = $path_func($code);
            array_walk($GLOBALS['pipline'],function($item,$key){
                $key = explode('_samekey_',$key)[0];
                $class = new \ReflectionClass($key);
                foreach ($item as $v) {
                    $v = explode('.',$v)[0];
                    echo $class->getconstant($v) . "\n";
                }
            });
        return $response;
    }
}