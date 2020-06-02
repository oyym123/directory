<?php
/**
 * Created by PhpStorm.
 * User: Alienware
 * Date: 2020/6/1
 * Time: 23:07
 */

require 'common.php';
createDirectory();
if (isset($_GET['a']) && $_GET['a'] == 'content') {
    catchContent('template1');
}