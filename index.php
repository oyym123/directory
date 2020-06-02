<?php
/**
 * Created by PhpStorm.
 * User: Alienware
 * Date: 2020/6/1
 * Time: 23:07
 */

require 'common.php';

//设定规则 当进入这个页面的时候随机 繁殖多少个页面

$num = rand(10, 20);
createDirectory($num);
