<?php
/**
 * Created by PhpStorm.
 * User: Alienware
 * Date: 2020/6/1
 * Time: 23:53
 */

//抓取关键词
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

//抓取内容
function catchContent($template)
{
    $res = file_get_contents('./content.txt');
    catchKeyWords($template);
    rewrite($res, $template);
}

//写入内容
function rewrite($content, $template)
{
    require './jump.php';
    require './' . $template . '/cms.php';
    jump($template);
    echo $content;
}

//目录繁殖
function createDirectory()
{
    //原理是将 模板 不断的复制 生成的大批量的二级目录
    $templateStr = "['";

    for ($i = 1; $i < 10; $i++) {
        $templateStr .= 'template' . $i . "','";
    }

    $templateStr = substr($templateStr, 0, -2) . ']';
    for ($i = 1; $i < 10; $i++) {
        copyDir('./template', './template' . $i);
        file_put_contents('./template' . $i . '/cms.php', "<?php
echo '<h1>模板" . $i . "号</h1>';");

        file_put_contents('./template' . $i . '/index.php', "<?php
require './common.php';
catchContent('template" . $i . "');");

        file_put_contents('./template' . $i . '/config.php', "<?php
const DIR_ARR = " . $templateStr . ";");
    }
}

function copyDir($dirSrc, $dirTo)
{
    if (is_file($dirTo)) {
        echo $dirTo . '这不是一个目录';
        return;
    }
    if (!file_exists($dirTo)) {
        mkdir($dirTo);
    }

    if ($handle = opendir($dirSrc)) {
        while ($filename = readdir($handle)) {
            if ($filename != '.' && $filename != '..') {
                $subsrcfile = $dirSrc . '/' . $filename;
                $subtofile = $dirTo . '/' . $filename;
                if (is_dir($subsrcfile)) {
                    copyDir($subsrcfile, $subtofile);//再次递归调用copydir
                }
                if (is_file($subsrcfile)) {
                    copy($subsrcfile, $subtofile);
                }
            }
        }
        closedir($handle);
    }

}



