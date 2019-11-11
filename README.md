# find-java-api
一些简单的代码查找
### 引入
**使用composer引入**

`$ composer require jack-laoniu/find-java-api`
### 配置
**在config/app.php中添加** `SelfTools\FindJavaApi\FindJavaApiProvider::class`

### 使用
```
public function getTest(Request $request)
    {
        return app('FindJavaApi')->fuck($request);
    }
```
### 扩展
类似laravel的管道
在`config/piplines`中添加`lines`
### License
MIT
