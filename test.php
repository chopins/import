<?php
date_default_timezone_set('UTC');
include 'src/import.php';
//导入类
import('example\testAClass');
//导入常量
$a = new $testAClass;

import('example\testAClass\CONST_VAR');
echo CONST_VAR;