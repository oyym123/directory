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
        if ($num[0] == $key + 2) {
            echo '<title>泛目录之关键词：' . $re . '</title>';
            echo '<meta><h1>关键词：' . $re . '</h1></meta>';
        }
    }
}

function catchKey($key)
{
    $url = 'http://suggestion.baidu.com/su?wd=' . $key . '&p=3&cb=window.bdsug.sug';
    header("content-type: text/html; charset=utf-8");
    $res = httpRequest($url);
    $content = iconv("gbk", "utf-8", $res);
    preg_match('/window.bdsug.sug((.*?));/', $content, $return);
    $return = $return[1];
    $return = explode('[', $return);
    $return = '[' . str_replace('})', '', $return[1]);
    $r = json_decode($return, true);

    $str = '';
    foreach ($r as $item) {
        $str .= PHP_EOL . $item;
    }
    file_put_contents('./keywords.php', $str, FILE_APPEND);
}


function getPath($fileName = '')
{
    if (PHP_OS == "Linux") {
        $res = explode('/', __DIR__);
    } else {
        $res = explode('\\', __DIR__);
    }

    unset($res[(count($res) - 1)]);
    $res = array_merge($res, [$fileName]);
    if (PHP_OS == "Linux") {
        $res = implode('/', $res);
    } else {
        $res = implode('\\', $res);
    }
    return $res;
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
function createDirectory($num = 10)
{
    //原理是将 模板 不断的复制 生成的大批量的二级目录
    $templateStr = "['";

    for ($i = 1; $i <= $num; $i++) {
        $templateStr .= 'template' . $i . "','";
    }

    $templateStr = substr($templateStr, 0, -2) . ']';
    for ($i = 1; $i <= $num; $i++) {
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


/**
 * CURL请求
 * @param $url 请求url地址
 * @param $method 请求方法 get post
 * @param null $postfields post数据数组
 * @param array $headers 请求header信息
 * @param bool|false $debug 调试开启 默认false
 * @return mixed
 */
function httpRequest($url, $method = "GET", $postfields = null, $headers = array(), $debug = false)
{
    $method = strtoupper($method);
    $ci = curl_init();
    /* Curl settings */
    curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    curl_setopt($ci, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.2; WOW64; rv:34.0) Gecko/20100101 Firefox/34.0");
    curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 60); /* 在发起连接前等待的时间，如果设置为0，则无限等待 */
    curl_setopt($ci, CURLOPT_TIMEOUT, 7); /* 设置cURL允许执行的最长秒数 */
    curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
    switch ($method) {
        case "POST":
            curl_setopt($ci, CURLOPT_POST, true);
            if (!empty($postfields)) {
                $tmpdatastr = is_array($postfields) ? http_build_query($postfields) : $postfields;
                curl_setopt($ci, CURLOPT_POSTFIELDS, $tmpdatastr);
            }
            break;
        default:
            curl_setopt($ci, CURLOPT_CUSTOMREQUEST, $method); /* //设置请求方式 */
            break;
    }
    $ssl = preg_match('/^https:\/\//i', $url) ? TRUE : FALSE;
    curl_setopt($ci, CURLOPT_URL, $url);
    if ($ssl) {
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
        curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, FALSE); // 不从证书中检查SSL加密算法是否存在
    }
    //curl_setopt($ci, CURLOPT_HEADER, true); /*启用时会将头文件的信息作为数据流输出*/
    curl_setopt($ci, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ci, CURLOPT_MAXREDIRS, 2);/*指定最多的HTTP重定向的数量，这个选项是和CURLOPT_FOLLOWLOCATION一起使用的*/
    curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ci, CURLINFO_HEADER_OUT, true);
    /*curl_setopt($ci, CURLOPT_COOKIE, $Cookiestr); * *COOKIE带过去** */
    $response = curl_exec($ci);
    $requestinfo = curl_getinfo($ci);
    $http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
    if ($debug) {
        echo "=====post data======\r\n";
        var_dump($postfields);
        echo "=====info===== \r\n";
        print_r($requestinfo);
        echo "=====response=====\r\n";
        print_r($response);
    }
    curl_close($ci);
    return $response;
    //return array($http_code, $response,$requestinfo);
}




