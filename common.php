<?php
/**
 * Created by PhpStorm.
 * User: Alienware
 * Date: 2020/6/1
 * Time: 23:53
 */

function catchKeyWords()
{
    $res = file_get_contents('./keywords.txt');
    $res = explode(PHP_EOL, $res);
    echo '<pre>';
    print_r($res);
    exit;
}

function catchContent($template)
{
    $res = file_get_contents('./content.txt');
    rewrite($res,$template);
}

function rewrite($content, $template)
{
    require './jump.php';
    require './' . $template . '/cms.php';
    jump($template);
    echo $content;
}




