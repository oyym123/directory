<?php
/**
 * Created by PhpStorm.
 * User: Alienware
 * Date: 2020/6/1
 * Time: 23:53
 */

function catchKeyWords($template)
{
    $res = file_get_contents(getPath('keywords.php'));
    $res = explode(PHP_EOL, $res);
    preg_match("/\d+/", $template, $num);
    $cacheList = file_get_contents('./cache_list.php');
    $cacheList = explode(PHP_EOL, $cacheList);
    foreach ($cacheList as $item) {
        if (!empty($item)) {
            echo '<a href="cache/' . $item . '"><h5>缓存目录链接：' . $item . '</h5></a><br/>';
        }
    }
    foreach ($res as $key => $re) {
        if ($num[0] == $key + 1) {
            echo '<title>泛目录之关键词：' . $re . '</title>';
            echo '<meta content="' . $re . '"><h1>关键词：' . $re . '</h1></meta>';
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
    return $r;
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

function pa($page)
{
    echo '<a target="_blank" href="http://www.xiaole8.com/gushihui/"><button>原故事会网址</button></a>';
    echo '<h3>故事会分页列表，点击文章标题后自动生成缓存页面：</h3>';
    for ($i = 1; $i < 10; $i++) {
        echo '<h1><a style="float:left;" href="index.php?a=' . $i . '">' . $i . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a></h1>';
    }
    echo '<br>';
    echo '<br>';

    $url = 'http://www.xiaole8.com/gushihui/page_' . $page . '.html';
    $res = httpRequest($url);
    $starInfo = iconv('GBK', 'UTF-8//IGNORE', $res);
    preg_match("/cright(.*?)pages/is", $starInfo, $info);

    preg_match_all("/<a href=(.*?)<\/span>/", $info[0], $i);

    foreach ($i[1] as $item) {
        preg_match("/target=\"_blank\">(.*?)20/is", $item, $title);
        preg_match("/\"(.*?)\" ta/is", $item, $url);
        $u = 'index.php?url=' . $url[1];
        echo '<a href="' . $u . '"><h2>' . $title[1] . '</h2></a><br/>';
    }
}

function catchContent($template)
{
    $res = file_get_contents('./content.txt');
    rewrite($res, $template);
}

function detail($url, $template)
{
    $res = httpRequest($url);
    $starInfo = iconv('GBK', 'UTF-8//IGNORE', $res);
    $u = substr($url, 0, strrpos($url, '/'));

    preg_match("/wzview(.*?)wzad1/iUs", $starInfo, $content);
    $content = str_replace(['https', 'wzview">', '上一页', '下一页', '<div class="wzad1', '<a href=\''], ['', '', '', '', '', '<a href=\'?detail=' . $u . '/'], $content);

    //写入关键词
    $res = file_get_contents(getPath('keywords.php'));
    $res = explode(PHP_EOL, $res);
    preg_match("/\d+/", $template, $num);
    $str = '';
    foreach ($res as $key => $re) {
        if ($num[0] == $key + 1) {
            $str .= '<title>泛目录之关键词：' . $re . '</title>';
            $str .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"><h1>关键词：' . $re . '</h1></meta>';
        }
    }
    $result = replaceWords($content[0]);
    $strCache = '';
    $strCache .= $str;
    $strCache .= '<a href="../"><h1>返回主页</h1></a>';
    $strCache .= $result;
    $strCache .= '<a href="../"><h1>返回主页</h1></a>';

    $str .= '<a href="index.php"><h1>返回主页</h1></a>';

    $str .= $result;
    $str .= '<a href="index.php"><h1>返回主页</h1></a>';
    echo $str;

    $fileName = md5(time() + rand(99999, 999999)) . '.html';

    //生成本地静态缓存页面
    file_put_contents('./cache/' . $fileName, $strCache);

    //将缓存页链接写入到缓存名称文件当中
    file_put_contents('./cache_list.php', $fileName . PHP_EOL, FILE_APPEND);
    exit;
}

//替换文章词语 实现伪文章发布
function replaceWords($content)
{
    include getPath('synonym.php');
    $str = str_replace(array_keys($synWorld), array_values($synWorld), $content);
    return $str;
}

function rewrite($content, $template)
{
    require './jump.php';
    require './cms.php';
    catchKeyWords($template);
    jump($template);

    if (isset($_GET['a']) && !empty($_GET['a'])) {
        pa($_GET['a']);
    } else {
        if (isset($_GET['url']) && !empty($_GET['url'])) {
            detail($_GET['url'], $template);
        }
        if (isset($_GET['detail']) && !empty($_GET['detail'])) {
            detail($_GET['detail'], $template);
        }
        pa(1);
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



