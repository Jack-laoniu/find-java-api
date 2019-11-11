<?php
/**
 * Created by PhpStorm.
 * User: bluefish
 * Date: 2019/8/15
 * Time: 下午5:38
 */

namespace SelfTools\FindJavaApi;

use App\Common\FindJava\PublicCode;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Symfony\Component\Routing\Route as SymfonyRoute;
use Illuminate\Support\Arr;

class FindJavaLogic {
    public static $validators;

    public $pips = [
        'SelfTools\FindJavaApi\FindJava\FindExcute',
        'SelfTools\FindJavaApi\FindJava\FindLibrary',
//        'FindJava\FindSelfLibrary',
        'SelfTools\FindJavaApi\FindJava\FindPath'
    ];


    protected $table = 'test2';
//    protected $dates = ['email','created_at'];
    protected $fillable = [];
//    protected $dates = ['email'];



    public function fuck($request)
    {
        $route = $request->input('route');
        $method = $request->input('method');
        $routeIsset = $this->routeIsset($route,$method);
        if(is_array($routeIsset)){
            $GLOBALS['pipline'] = PublicCode::findJavaApiRoute($routeIsset['controller'],'@',true,true,true);
        }else{
            return $routeIsset;
        }
        return $this->testArrayReduce($GLOBALS['pipline']);
    }
    public function findJavaApiRoute($ActionName,$delimiter,$is_anti = false)
    {
        list($class , $method) = explode($delimiter,$ActionName);
        $function_list = new \ReflectionMethod($class,$method);
        $path2 = app_path().strstr($ActionName, $delimiter,true);
        if($is_anti){
            $path2 = app_path().str_replace('\\','/',substr($class,3));
        }
        $code  = $this->getFileLines($path2.'.php',$function_list->getStartLine(),$function_list->getEndLine());
        return $code;
    }
    public  function fuckyou(&$path){
        return strtolower(substr($path,0,3));
    }
    public function getFileLines($filename, $startLine = 1, $endLine = 50, $method = 'rb'){
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
        return array_filter($content); // array_filter过滤：false,null,''
    }
    /**
     *
     * User: bluefish
     * Date: 2019/7/1
     * Time: 下午4:17
     * @param $route
     * @return mixed
     */
    public function routeIsset($route,$method)
    {
        if(!preg_match('/^\/?(([a-z]+-?[a-z]+\/)|[a-z]+-?[a-z]+)+$/',$route) || empty($route)){
            return 'really? route path is not real';
        }
        if(empty($method)){
            return 'necessary method ';
        }
        $route = substr($route,0,1) == '/'?substr($route,1):$route;
        $result = $this->routeObj($route,$method);
        if(!$result || trim(strstr($result->getAction()['controller'],'@'),'@') == 'missingMethod'){
            return '404';
        }else{

            return $result->getAction();
        }
    }

//    public function routeObjAttributes($attributes)
//    {
//        return call_user_func(array($this,'routeObj'),$attributes);
//    }

    public function routeObj($routename,$method)
    {
//        dd(app('router')->has('event/bind'));
//        dd(app('router')->get('POST'));
        $result =  Arr::first(app('router')->getRoutes(), function ($key, $value) use ($routename,$method) {
            $uri = preg_replace('/\{(\w+?)\?\}/', '{$1}', $value->getPath());
//            return $route->methods();
            $compiled = (new SymfonyRoute($uri,$this->fuckparam($uri)))->compile();
            $routename = $routename == '/' ? '/' : '/'.$routename;
            //laravel 5.2  Route::controller 这种路由申明方式 真的 恶心没办法得处理一下
            $routename2 = $routename.'/test/test/test/test/test';
            if((preg_match($compiled->getRegex(), rawurldecode($routename2)) || preg_match($compiled->getRegex(), rawurldecode($routename)))){
                if($value->getMethods()[0] == strtoupper($method)){
                    return true;
                }
            }
        });
        return $result;
//        $compiled = '';
////        $staticPrefix = $compiled->where('staticPrefix');
//        $result = collect(app('router')->getRoutes())->map(function($route) use ($routename,$compiled) {
//            $uri = preg_replace('/\{(\w+?)\?\}/', '{$1}', $route->getPath());
////            return $route->methods();
//            $compiled = (new SymfonyRoute($uri,$this->fuckparam($uri)))->compile()->getRegex();
//            $routename = $routename == '/' ? '/' : '/'.$routename;
//            return $compiled;
//            return preg_match($compiled->getRegex(), rawurldecode($routename));
////            foreach ($this->getValidators() as $validator) {
////                if (! $includingMethod && $validator instanceof MethodValidator) {
////                    continue;
////                }
////
////                if (! $validator->matches($this, $request)) {
////                    return false;
////                }
////            }
//
////            return true;
////            if($route->getCompiled()){
////                return $route->getCompiled()->getRegex();
////            }else{
////                return $route;
////            }
//        })->get(104);



//
//            ->where('uri','event/bind');
//            ->where('uri','apis/schedule/init/{one?}/{two?}/{three?}/{four?}/{five?}'));
//            ->whereLoose('uri',$routename);
    }

    public static function getValidators()
    {
        if (isset(static::$validators)) {
            return static::$validators;
        }

        // To match the route, we will use a chain of responsibility pattern with the
        // validator implementations. We will spin through each one making sure it
        // passes and then we will know if the route as a whole matches request.
        return static::$validators = [
            new MethodValidator, new SchemeValidator,
            new HostValidator, new UriValidator,
        ];
    }
    public function fuckparam($uri)
    {
        preg_match_all('/\{(\w+?)\?\}/', $uri, $matches);

        return isset($matches[1]) ? array_fill_keys($matches[1], null) : [];
    }
    /**
     *
     * User: bluefish
     * Date: 2019/7/19
     * Time: 上午11:31
     * @return mixed
     */
    public function getJavaApiRoute($code)
    {
//        array_walk($code, function(&$str_line){
//            if (strpos($str_line,'acquire') !== false) {
//                $str_line = $str_line.'fuck';
//                return $str_line;
//            }
//        });
//        if($result = collect($code)->search(function($item,$key){
//            return strpos($item,'acquire') !== false;
//        })){
//            return $code[$result];
//        }else{
//            return 'not find';
//        }
        return array_reduce(config('special_str.B.java_curl'),function ($stack, $pipe) use ($code){
            if ($pipe instanceof Closure) {
                return call_user_func($pipe, $code, $stack);
            }
//            else{
//                $classReflection = new \ReflectionClass($pipe);
//                if($classReflection){
//
//                    return $this->findJavaApiRoute($code,'@');
//                }else{
//                    return 'not find';
//                }
//            }
        },function($path_func) use ($code){
            if($result = collect($code)->search(function($item,$key){
                return strpos($item,'acquire') !== false;
            })){
                return $code[$result];
            }else{
                return $path_func($code);
            }
        });
    }

    public function testArrayReduce($code)
    {
//        $functions = array_merge(config('special_str.B.java_curl') , config('special_str.B.final_func'));
        return (new Pipeline(new \Illuminate\Container\Container()))->send($code)->through($this->pips)->then(function($code){
            $fuck_code = array();
            array_walk($code,function(&$item,$key) use ($code,&$fuck_code){
                if($item){
                    foreach ($item as $k => $v){
                        if(preg_match("/javaCurl\(/", $v)){
                            preg_match_all("/(?<=(self::)).*?(?=(\;))/", $item[$k-1], $result);
                            if(!empty($result[0])){
                                $fuck_code[$key][] = $result[0][0];
                            }
                        }elseif (preg_match("/(execute|excute)\(/", $v)){
//                            preg_match_all("/self::([^()]+|(?R))*\,/", $item[$k], $result);
                            preg_match_all("/(?<=((\(|\.)(self::|\$\this\-\>))).*?(?=(\,))/", $item[$k], $result);
                            if(!empty($result[0])){
                                $fuck_code[$key][] = $result[0][0];
                            }
                        }
                    }
                }
            });
            if(count($fuck_code) == 0){
                dd('php no need java');
            }
            $GLOBALS['pipline'] = $fuck_code;

//            if($result = collect($code)->search(function($item,$key){
//                return strpos($item,'acquire') !== false;
//            })){
//                $GLOBALS['pipline'] = $code[$result];
//            }else{
//                return 'php no need java';
//            }
        });
    }

    public  function  searchCode($code,$needle,$return = 'value')
    {
        $result_code = [];
        array_walk($code,function ($value,$key,$needle) use ($return,&$result_code){
            if(preg_match('/^([A-Za-z]+\\\\)+'.$needle.'$/',($return == 'value')?$value:$key)){
                $result_code[] = ($return == 'value')?$value:$key;
            }
        },$needle);
        return $result_code;
    }
    public function testReduceback($stack, $pipe)
    {
        return function ($passable) use ($stack, $pipe) {
            if ($pipe instanceof Closure) {
                return call_user_func($pipe, $passable, $stack);
            }else{
                $classReflection = new \ReflectionClass($pipe);
                if($classReflection){
                    return $this->findJavaApiRoute($pipe,'@');
                }else{
                    return 'not find';
                }
            }
        };
    }
}