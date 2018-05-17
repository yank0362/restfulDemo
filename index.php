<?php
/**
 * Created by PhpStorm.
 * User: Connie
 * Date: 2018/5/14
 * Time: 10:55
 */

require_once __DIR__."/lib/User.php";
require_once __DIR__."/lib/Article.php";

$pdo = require __DIR__."/lib/db.php";

//$user = new User($pdo);
//print_r($user->register('connie','111111'));
//print_r($user->login('connie','111111'));

$article = new Article($pdo);

print_r($article->getList(1,1,2));