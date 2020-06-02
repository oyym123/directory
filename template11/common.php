<?php
/**
 * Created by PhpStorm.
 * User: Alienware
 * Date: 2020/6/1
 * Time: 23:53
 */

function catchKeyWords($template)
{
    $res = file_get_contents('./keywords.txt');
    $res = explode(PHP_EOL, $res);
    preg_match("/\d+/", $template, $num);

    foreach ($res as $key => $re) {
        if ($num[0] == $key + 1) {
            echo '<title>泛目录之关键词：' . $re . '</title>';
            echo '<meta><h1>关键词：' . $re . '</h1></meta>';
        }
    }
}

function catchContent($template)
{
    $res = file_get_contents('./content.txt');

    rewrite($res,$template);
}

function rewrite($content, $template)
{
    require './jump.php';
    require './cms.php';
    catchKeyWords($template);
    jump($template);
    echo $content;
}




