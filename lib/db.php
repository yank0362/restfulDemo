<?php
/**
 * 连接数据库返回数据库连接句柄
 * Created by PhpStorm.
 * User: Connie
 * Date: 2018/5/14
 * Time: 11:48
 */
$pdo = new PDO("mysql:host=localhost;dbname=mydb",'root','1q2w3e4r');
$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);//关闭模拟预处理
$pdo->query("set names utf8;");
return $pdo;