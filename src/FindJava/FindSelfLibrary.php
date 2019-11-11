<?php
/**
 * Created by PhpStorm.
 * User: bluefish
 * Date: 2019/9/8
 * Time: 下午6:01
 */

namespace SelfTools\FindJavaApi\FindJava;


class FindSelfLibrary {

    public function handle($code,$path_func)
    {
        array_walk($code,function(&$item,$key) use(&$code) {
            if($item){
                $code_self_regex = '/self::[A-Za-z]+\(/';
                $need_code = PublicCode::searchCode($item,$code_self_regex,'value',false,false);
                foreach ($need_code as $v){
                    preg_match_all("/self::([^()]+|(?R))*\(/", $v, $result);
                    if(!empty($result[1])){
                        $key = explode('_samekey_',$key)[0];
                        $machine_array = PublicCode::findJavaApiRoute($key,$result[1][0],true,false);
//                        dd($item);
                        array_push($item,...$machine_array);
                    }
                }
            }
        });
        return $path_func($code);
    }
}