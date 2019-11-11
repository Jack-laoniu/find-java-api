<?php
/**
 * Created by PhpStorm.
 * User: bluefish
 * Date: 2019/8/15
 * Time: 下午5:44
 */

namespace SelfTools\FindJavaApi\FindJava;
use Illuminate\Support\Arr;


class PublicCode {
    const COMPOSER_REGEX = '/^([A-Za-z]+\\\\)+Intf|Logics(\\\\[A-Za-z]+)+$/';
    const CODE_LOGIC_REGEX = '?Logic?';
    const CODE_INT_REGEX = '?Int(\(|::)?';
    const CODE_SELF_REGEX = '/self::[A-Za-z]+\(/';
    public static function  searchCode($code,$regex,$return = 'value',$is_composer,$is_int=false)
    {
        $result_code = [];
        $int_regex = [];
        array_walk($code,function ($value,$key) use ($code,$return,&$result_code,$regex,$is_composer,$is_int,$int_regex){
            if(preg_match($regex,($return == 'value')?$value:$key)){
                $value = str_replace('"','\'',$value);
                if($is_int){
//                    preg_match_all('',($return == 'value')?$value:$key,$result);
                    if(array_get(explode('=',$value),0) && strpos($value,'=') && !strpos($value,'->')){
                        $second_way = '\\'.trim(array_get(explode('=',$value),0),' ');
                        $GLOBALS['int_regex'][] = $second_way;
                        $result_code[] = $return == 'value'?$value:$key;
                    }else{
                        list($default_class,$default_method) = explode('::',$return == 'value'?$value:$key);
                        $methods = explode('->',$default_method);
                        if(count($methods) >= 2){
                            $result_code[] = $default_class.'::'.array_get($methods,1);
                        }else{
                            $result_code[] = $return == 'value'?$value:$key;
                        }
                    }
                }elseif($is_composer){
                    $result_code[] = $return == 'value'?$value:$key;
                }else{
                    if(count(explode('::',$return == 'value'?$value:$key)) >= 2){
                        list($default_class,$default_method) = explode('::',$return == 'value'?$value:$key);
                        if(count($methods = explode('()->',$default_method)) >= 2){
                            $result_code[] = $default_class.'::'.$methods[1];
                        }else{
                            $result_code[] = $return == 'value'?$value:$key;
                        }
                    }else{
                        $result_code[] = $return == 'value'?$value:$key;
                    }
                }
            }elseif(!empty($GLOBALS['int_regex'])){
                $int_regex_str = '/'.implode('||',$GLOBALS['int_regex']).'/';
                if(preg_match($int_regex_str,($return == 'value')?$value:$key)) {
                    $result_code[] = $return == 'value'?$value:$key;
                }
            }
        });
        return $result_code;
    }

    /**
     *
     * User: bluefish
     * Date: 2019/11/1
     * Time: 2:31 PM
     * @param $ActionName
     * @param $delimiter
     * @param bool $is_anti
     * @param bool $is_delimiter
     * @return array|string
     * @throws \ReflectionException
     */
    public static function findJavaApiRoute($ActionName, $delimiter, $is_anti = false ,$is_delimiter = true,$is_execute = false)
    {
        if($is_delimiter){
            list($class , $method) = explode($delimiter,$ActionName);
        }else{
            list($class , $method) = [$ActionName,$delimiter];
        }
        $function_list = new \ReflectionMethod($class,$method);
        $path2 = app_path().$class;
        if($is_anti){
            $class = substr($class,0,1) == '\\'?substr($class,1):$class;
            $class_arr = explode('\\',$class);

            unset($class_arr[0]);

            $path2 = app_path().'/'.implode('/',$class_arr);
        }
//        if($ActionName == 'App\Intf\GiftInt'){
//            dd($path2);
//        }
        $code  = self::getFileLines($path2.'.php',$function_list->getStartLine(),$function_list->getEndLine());
        $GLOBALS['current_controller'] = $class;
        return $code;
    }
    public static function getFileLines($filename, $startLine = 1, $endLine = 50, $method = 'rb'){
        $content = array();

        $fp = fopen($filename, $method);
        if (!$fp)
            return 'error:can not read file';
        for ($i = 1; $i < $startLine; ++$i) { // 跳过前$startLine行
            fgets($fp);
        }

        for ($i; $i <= $endLine; ++$i) {
            $content[] = fgets($fp); // 读取文件行内容
        }
        fclose($fp);
        $empty_key = [];
        array_walk($content,function(&$item,$key) use (&$empty_key){
            $filter_str = ["\t"," ","\n","\x0B","\r","\0","\r\n"];
            $item = str_replace($filter_str,'',$item);
        });
        foreach($content as $k => $v){
            if(empty($v)){
                unset($content[$k]);
            }
        }
        $content = array_values($content);
        return $content;
    }

    public static function getRelationship($need_code,$class,$self_class = null)
    {
        $machine_array = array();
        $need_code_array = array();
//        $class = Arr::where($class,function($key, &$value) use ($need_code,&$machine_array){
//            $use_value = substr(strrchr($value, "\\"), 1);
//            foreach ($need_code as $kk=> $vv){
//                if(strpos($vv,$use_value)){
//                    if(strpos($vv,'::')){
//                        preg_match_all("/".$use_value."::([^()]+|(?R))*\(/", $vv, $result);
//                        $machine_array[] = empty($result[1])?null:$result[1][0];
//                    }else{
//                        $second_way = '\\'.trim(array_get(explode('=',$vv,-1),0),' ');
//                        preg_match_all("/".$second_way."\-\>([^()]+|(?R))*\(/", array_get($need_code,$kk+1), $result2);
//                        $machine_array[] = empty($result2[1])?null:$result2[1][0];
//                    }
//                    return true;
//                }
//            }
//        });
        //垃圾代码

//        $need_code = array_map(function($value) use (&$need_code,$class,&$machine_array,&$need_code_array){
//            $machine_array = Arr::where($class,function ($kk,$vv) use ($value,&$need_code_array,&$need_code){
//                $use_value = substr(strrchr($vv, "\\"), 1);
//                if(strpos($value,$use_value)){
//                    if(strpos($value,'::')){
//                        preg_match_all("/".$use_value."::([^()]+|(?R))*\(/", $value, $result);
//                        $need_code_array[] = empty($result[1])?null:$result[1][0];
//                    }else{
//                        $second_way = '\\'.trim(array_get(explode('=',$value,-1),0),' ');
//                        preg_match_all("/".$second_way."\-\>([^()]+|(?R))*\(/", next($need_code), $result2);
//                        $need_code_array[] = empty($result2[1])?null:$result2[1][0];
//                    }
//                    //next 有问题 现在 $need_code_array 变量中 数据太多了
//                    return true;
//                }
//            });
            foreach ($need_code as $key=>$value){

                $machine_array[] = Arr::where($class,function ($kk,$vv) use ($value,&$need_code_array,&$need_code,$key,$self_class){
                    if(preg_match(self::CODE_SELF_REGEX,$value)){
                        preg_match_all("/self::([^()]+|(?R))*\(/", $value, $result);
                        $need_code_array[] = empty($result[1])?null:$result[1][0];
                    }
                    $use_value = substr(strrchr($vv, "\\"), 1);
                    if(strpos($value,$use_value)){
                        if(strpos($value,'::')){
                            preg_match_all("/".$use_value."::([^()]+|(?R))*\(/", $value, $result);
                            $need_code_array[] = empty($result[1])?null:$result[1][0];
                        }else{
                            $second_way = '\\'.trim(array_get(explode('=',$value,-1),0),' ');
                            preg_match_all("/".$second_way."\-\>([^()]+|(?R))*\(/", array_get($need_code,$key+1), $result2);
                            $need_code_array[] = empty($result2[1])?null:$result2[1][0];
                        }
                        return true;
                    }
                });
            }


        $machine_array = array_reduce($machine_array, 'array_merge', array());
        if($self_class !== null){
            $i = 0;
            if(is_numeric($self_class)){
                $self_class = array_get($GLOBALS, 'current_controller');
            }
            $need_code_array = array_unique($need_code_array);
            foreach ($machine_array as $machine_key => $machine_value){
                $machine_array[$machine_key] = $machine_value.'_samekey_'.$machine_key;
            }

            while($i < count($need_code_array)){
                $machine_array[] = $self_class.'_samekey_'.$i;
                $i++;
            }

        }
        if(count($need_code_array) != count($machine_array)){
            $i = 0;
            while ($i <= (count($machine_array)-count($need_code_array))){
                $need_code_array[] = null;
                $i++;
            }
        }
//        dd([$machine_array,$need_code_array,count($need_code_array),count($machine_array)]);
        $final_array = array_combine($machine_array,$need_code_array);

        foreach ($final_array as $k => $v){
            if(empty($v)) unset($final_array[$k]);
        }
        return $final_array;
    }

    public static function recursiveGetLibrary($code,$class,$result = [])
    {
        //todo 现在 思路是 获取 composer——map 中 所有 的 logic 和 intf 文件 与 code 比较 重合的 拿出 ，并且code 中的 class与 method 对应
        //然后在此方法中 找到 对应方法 代码  找寻 特定调用java 接口方法 如果方法代码中 还有 对应logic 或者 intf 的调用就再次执行上边的 思路 指到没有
        if (count($code) == count($code, COUNT_RECURSIVE)){
            $empty_array = [];
            array_push($empty_array,$code);
            $code = $empty_array;
        }
        foreach ($code as $key=>$value){
            if($value){
                $need_logic_code = PublicCode::searchCode($value, self::CODE_LOGIC_REGEX, 'value', false);
                $need_self_code = PublicCode::searchCode($value,self::CODE_SELF_REGEX,'value',false,false);
                $need_int_code = PublicCode::searchCode($value, self::CODE_INT_REGEX, 'value', false, true);
                $need_code = array_merge($need_logic_code, $need_int_code,$need_self_code);
                //使用 array_combine 去 吧 class 里的 中的 全路径的 logics 或者 inft 放在一个数组中 ，在 吧 这个need_code
                //中 对应的 类方法 按顺序找到 ，combine 到一起 就是我们要的东西
                if(!empty($need_self_code)){
                    $Relationship = PublicCode::getRelationship($need_code, $class ,$key);
                }else{
                    $Relationship = PublicCode::getRelationship($need_code, $class);
                }
                if (count($Relationship) > 0) {
//                    dd($Relationship);
                    /** todo
                     *现在是 self  类型的 搜索 没有对应的 类 ，直接是 0
                     */
                    array_walk($Relationship, function (&$item, $key) use (&$Relationship) {
                        $key = explode('_samekey_',$key)[0];
                        $fuck_class = new \ReflectionClass($key);
                        if ( !$fuck_class->hasMethod($item)) {
                            $item = null;
                        } else {
                            $item = PublicCode::findJavaApiRoute($key, $item, true, false);
                        }
                    });

                    array_push($result,$Relationship);
//                    if(!empty($need_self_code)){
//                        dd($result);
//                    }
                    return self::recursiveGetLibrary($Relationship,$class,$result);
                }
            }
        }
        return $result;
    }
}