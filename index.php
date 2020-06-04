<?php
/**
 * Created by PhpStorm.
 * User: Alienware
 * Date: 2020/6/1
 * Time: 23:07
 */
require 'common.php';

//设定规则 当进入这个页面的时候随机 繁殖多少个页面
$num = 10;
createDirectory($num);

if(isset($_GET['key'])){
    //抓取百度下拉框关键词
    $randKey = [
        '六一儿童节',
        '美国暴乱',
        '传闻中的陈芊芊',
        '川建国',
        '窃·格瓦拉',
        '驰名双标',
        '祖安人',
        '送口罩',
        '集美',
        'PUA',
    ];

    foreach ($randKey as $item) {
        $keyWord = catchKey($item);
    }
}
