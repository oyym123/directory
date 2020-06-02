<?php
/**
 * Created by PhpStorm.
 * User: Alienware
 * Date: 2020/6/1
 * Time: 23:38
 */
const DIR_ARR = ['template1', 'template2', 'template3', 'template4'];  //所有主目录

function jump($template)
{
    $res = chainWheel($template);
    foreach ($res as $key => $re) {
        echo '<a href="'.$re['url'] .'"><h3>' . $re['url'] . '</h3></a>';
    }
}

//生成轮链
function chainWheel($template)
{
    preg_match("/\d+/", $template, $num);
    $chainArr = [];
    //若将1作为主入口 则 2->1  3->2&1
    foreach (DIR_ARR as $key => $item) {
        if ($key < $num[0] && $key != 0) {
            $chainArr[] = [
                'url' => 'http://' . $_SERVER['HTTP_HOST'] . '/template' . $key . '/index.php'
            ];
        }
    }
    return $chainArr;
}