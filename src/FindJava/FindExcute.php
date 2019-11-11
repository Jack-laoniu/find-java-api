<?php
/**
 * Created by PhpStorm.
 * User: bluefish
 * Date: 2019/8/15
 * Time: 下午5:52
 */

namespace SelfTools\FindJavaApi\FindJava;
use Illuminate\Support\Arr;


class FindExcute {
    public function handle($code,$path_func)
    {
//        if($result = collect($code)->search(function($item,$key){
//            return strpos($item,'execute') !== false;
//        })){
//            return $code[$result];
//        }else{
//            return $path_func($code);
//        }
        $GLOBALS['first_pipline'] = [];
        $current_controller = array_get($GLOBALS, 'current_controller');
        //为了解决self：：这种的调用可以找到 对应的 类名
        /*也是controller 进去后直接调用 curl 的 这种的 想找到 curl 方法的类 就必须 去在 composer引入类时候记录下来
            但是鉴于之后的 array_push 会有冲突 所以 这个边直接 放在全局变量中
        */
        unset($code['current_controller']);
        $code = Arr::where($code,function ($k,$v) use($current_controller){
           if(strpos($v,'execute') !== false) {
//               preg_match_all("/(?<=(execute\((self::|\$\this\-\>))).*?(?=(\,))/", $v, $result);
//               if(!empty($result[0])){
//                   $v = $result[0][0];
//               }
                $GLOBALS['first_pipline'][] = array($current_controller=>array($v));
               return false;
           }else{
               return true;
           }
        });
        if(count($GLOBALS['first_pipline'])){
            $GLOBALS['first_pipline'] = array(call_user_func_array('array_merge_recursive',$GLOBALS['first_pipline']));
        }
        $path_func($code);
    }
}