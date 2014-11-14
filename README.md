####介绍
import函数用来导入项目类，函数，常量到一个别名变量。

####License
New BSD License

####用法
`import()`导入时，会先在本函数被调用的文件所在目录路径与被导入文件是否拥有相同路径，有则去掉从最先相同处开始搜索文件，没有则从调用的文件目录开始搜索，然后在`include_path`目录搜索文件
搜索过程中文件的优先级高于目录。PHP文件高于动态库

1. 导入类： 
文件`/home/path/Namespace/ClassA.php`定义了`ClassA`:  
```php
namespace Namespace;
class ClassA {}
```

在文件`/home/path/call1.php`导入上面的类`ClassA`:     
```php
import('Namespace\ClassA');
$a = new $ClassA; //new Namespace\ClassA;
```

在文件`/home/path/Namespace/call2.php`导入上面的类`ClassA`:  
```php
import('Namespace\ClassA');
$a = new $ClassA; //new Namespace\ClassA;
```
但是在`/home/calltest.php`中将无法导入上面的类,除非`/home/path/`加入了`include_path`中  

批量导入类文件，`/home/path/Namespace/ClassA.php`定义了`Namespace\ClassA`,`/home/path/Namespace/ClassB.php`定义了`Namespace\ClassB`, 那么在`/home/path/call3.php`应该如下定义:    
```php
import('Namespace\*');
new $ClassA; //new Namespace\ClassA;
new $ClassB; //new Namespace\ClassB
```
一个文件中定义了多类：如`/home/path/Namespace/ClassAll.php`定义了`Namespace\ClassAll\ClassA`,`Namespace\ClassAll\ClassB`,`Namespace\ClassAll\ClassC`,那么在`/home/path/callAll.php`调用就应该这样:   
```php
import('Namespace\ClassAll\*');
new $ClassA; //new Namespace\ClassAll\ClassA
new $ClassB; //new Namespace\ClassAll\ClassB
new $ClassC; //new Namespace\ClassAll\ClassC
```

2.导入函数
文件`/home/path/Namespace/Function.php`定义了一些函数：  
```php
namespace Namespace\Function;
function A() {}
function B() {}
```

在文件`/home/path/call4.php`导入所有函数:  
```php
import('Namespace\Function\*');
$A(); // call A() function
$B(); // call B() function
```

3.导入常量与导入函数差不多，仅仅是导入了常量值

4.导入PHP扩展需要配置支持导入，并且扩展中有命名空间的定义
