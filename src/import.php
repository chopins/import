<?php

function import($name, $alias = '') {
    static $include_list = array();
    $import_key = md5($name);
    if(isset($include_list[$import_key])) {
        return;
    }
    $include_list[$import_key] = 1;
    $pwd = dirname(debug_backtrace(0 | DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]['file']);
    $p = explode('\\', $name);

    if (PHP_OS == 'Win') {
        $extension = '.dll';
    } else {
        $extension = '.so';
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
            if (__invoke__($name, $alias)) {
                return true;
            }
        }

        $extension_file = "{$zone}.{$extension}";

        if (file_exists($extension_file)) {
            if (dl($extension_file)) {
                if (__invoke__($name, $alias)) {
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
                    if (__invoke__($name, $alias)) {
                        return true;
                    }
                } else if ($ext == $extension) {
                    if (dl($path)) {
                        if (__invoke__($name, $alias)) {
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
    if(__invoke__($name, $alias)) {
        return true;
    } else {
        throw new RuntimeException("Can not import $name");
    }
}

function __invoke__($name, $alias) {
    if (class_exists($name, false)) {
        if (!empty($alias)) {
            class_alias($name, $alias, false);
            return true;
        }
    } else if (function_exists($name)) {
        if (!empty($alias)) {
            $GLOBALS[$alias] = function () use ($name) {
                $param_arr = func_get_args();
                call_user_func_array($name, $param_arr);
            };
        }
        return true;
    } else if (defined($name)) {
        if (!empty($alias)) {
            $GLOBALS[$alias] = constant($name);
        }
        return true;
    }
    return false;
}
