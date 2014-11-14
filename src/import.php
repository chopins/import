<?php

/**
 * import class,function, module from php file or php extension
 * import rule:
 * Namespace\SubNamespace\ClassName defined in Namespace/SubNamespace/ClassName.php
 * Namespace\SubNamespace\functionname defined in Namespace/SubNamespace.php 
 * <code>
 * //classA.php
 * <?php
 * class classA {
 * }
 * 
 * //call1.php
 * <?php
 * import('classA');
 * $a = new $classA;
 * 
 * //call2.php
 * <?php
 * import('classA',A);
 * $a = new $A;
 * 
 * // /home/path/MyNamespace/MySubNamespace/ClassB.php
 * <?php
 * namespace MyNamespace\MySubNamespace;
 * class ClassB {
 * }
 * 
 * //call3.php
 * <?php
 * import('MyNamespace\MySubNamespac\ClassB');
 * $b = new $ClassB;
 * 
 * //call4.php
 * <?php
 * import('MyNamespace\MySubNamespac\ClassB','B');
 * $b = new $B;
 * 
 * //call5.php
 * <?php
 * import('MyNamespace\MySubNamespac\ClassB',null,true);
 * $b = new MyNamespace\MySubNamespac\ClassB;
 * 
 * // /home/path/MyNamespace/MySubNamespace/funciton.php
 * <?php
 * namespace MyNamespace\MySubNamespace\funciton;
 * function funcA() {}
 * 
 * //call6.php
 * <?php
 * import('MyNamespace\MySubNamespace\funciton\funcA');
 * $funcA();
 * </code>
 * 
 * @staticvar array $include_list
 * @param string $name import the class or function, module,constant name
 * @param string $alias  set a alias name
 * @param boolean $origin whether use origin name
 * @return boolean
 * @throws RuntimeException
 */
function import($name, $alias = null, $origin = false) {
    static $include_list = array(), $extension = '';
    $import_key = md5($name);
    if (isset($include_list[$import_key])) {
        return;
    }
    $include_list[$import_key] = 1;
    $pwd = dirname(debug_backtrace(0 | DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]['file']);
    $p = explode('\\', $name);
    if ($extension === '') {
        if (substr(PHP_OS, 0, 3) == 'WIN') {
            $extension = '.dll';
        } else {
            $extension = '.so';
        }
    }
    $sep = DIRECTORY_SEPARATOR;
    $isdir = 0;
    foreach ($p as $file) {
        if (empty($file)) {
            continue;
        }

        if ($isdir) {
            $zone = "{$zone}{$sep}{$file}";
        } else {
            $zone = "{$pwd}{$sep}{$file}";
        }

        $include_php = "{$zone}.php";
        if (file_exists($include_php)) {
            include $include_php;
            if (__invoke__($name, $alias, $origin)) {
                return true;
            }
        }

        $extension_file = "{$zone}.{$extension}";

        if (file_exists($extension_file)) {
            if (dl($extension_file)) {
                if (__invoke__($name, $alias, $origin)) {
                    return true;
                }
            } else {
                throw new RuntimeException("Can not dl $extension_file");
            }
        }

        if (is_dir($zone)) {
            $isdir = 1;
            continue;
        }
        if (substr($file, -1) == '*') {
            foreach (glob($zone, GLOB_ONLYDIR | GLOB_ERR | GLOB_NOSORT) as $path) {
                $ext = pathinfo($path, PATHINFO_EXTENSION);
                if ($ext == 'php') {
                    include $path;
                    if (__invoke__($name, $alias, $origin)) {
                        return true;
                    }
                } else if ($ext == $extension) {
                    if (dl($path)) {
                        if (__invoke__($name, $alias, $origin)) {
                            return true;
                        }
                    } else {
                        throw new RuntimeException("Can not dl $path");
                    }
                }
            }
        }
    }
    $path = str_replace('\\', DIRECTORY_SEPARATOR, $name);
    include "{$name}.php";
    if (__invoke__($name, $alias, $origin)) {
        return true;
    } else {
        throw new RuntimeException("Can not import $name");
    }
}

function __invoke__($name, $alias, $origin = false) {
    if (empty($alias) && $origin === false) {
        $partlist = explode('\\', $name);
        $alias = end($partlist);
    }
    if (class_exists($name, false)) {
        if (!empty($alias)) {
            $GLOBALS[$alias] = $name;
            return true;
        }
    } else if (function_exists($name)) {
        if (!empty($alias)) {
            $GLOBALS[$alias] = function () use ($name) {
                $param_arr = func_get_args();
                call_user_func_array($name, $param_arr);
            };
            return true;
        }
    } else if (defined($name)) {
        if (!empty($alias)) {
            $GLOBALS[$alias] = constant($name);
            return true;
        }
    }

    return false;
}
