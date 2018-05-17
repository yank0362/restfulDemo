<?php
/**
 * Created by PhpStorm.
 * User: Connie
 * Date: 2018/5/14
 * Time: 11:48
 */
require_once __DIR__."/ErrorCode.php";
class User
{
    private $_db;//数据库连接句柄

    /**
     * User constructor.
     * @param PDO $_db
     */
    public function __construct($db)
    {
        $this->_db = $db;
    }


    /**
     * 用户登录
     * @param $username
     * @param $password
     * @return mixed
     * @throws Exception
     */
    public function login($username,$password){
        if(empty($username)){
            throw new Exception("用户名不能为空！",ErrorCode::USERNAME_CANNOT_EMPTY);
        }

        if(empty($password)){
            throw new Exception("密码不能为空！",ErrorCode::PASSWORD_CANNOT_EMPTY);
        }
        $password = $this->_md5($password);
        $sql = 'select * from `user` where `username` =:username and `password`=:password';
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(":username",$username);
        $stmt->bindParam(":password",$password);
        if(!$stmt->execute()){
            throw new Exception("服务器内部错误",ErrorCode::SERVER_INTERNAL_ERROR);
        }

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if(empty($user)){
            throw new Exception("用户名或密码错误！",ErrorCode::USERNAME_OR_PASSWORD_INVALID);
        }
        unset($user['password']);//删除密码字段
        return $user;
    }

    /**
     * 用户注册
     * @param $username
     * @param $password
     * @return array
     * @throws Exception
     */
    public function register($username,$password){
        if(empty($username)){
            throw new Exception("用户名不能为空！",ErrorCode::USERNAME_CANNOT_EMPTY);
        }

        if(empty($password)){
            throw new Exception("密码不能为空！",ErrorCode::PASSWORD_CANNOT_EMPTY);
        }


        if($this->_isUsernameExists($username)) {

            throw new Exception("用户名已存在！",ErrorCode::USERNAME_EXITS);
        }

        //写入数据库
        $sql = 'insert into `user`(`username`,`password`,`created_at`) values (:username,:password,:created_at)';
        $stmt = $this->_db->prepare($sql);
        $created_at = Date('Y-m-d H:i:s');
        $password = $this->_md5($password);
        $stmt->bindParam(":username",$username);
        $stmt->bindParam(":password",$password);
        $stmt->bindParam(":created_at",$created_at);
        if(!$stmt->execute()){
            throw new Exception("注册失败！",ErrorCode::REGISTER_FAIL);
        }

        return [
            "user_id"=>$this->_db->lastInsertId(),
            "username"=>$username,
            "created_at"=>$created_at,
        ];

    }


    /**
     * 检测用户名是否存在
     * @param $username
     * @return bool
     */
    private function _isUsernameExists($username){

        $sql = 'select * from `user` where `username` = :username';

        $stmt = $this->_db->prepare($sql);

        $stmt->bindParam(":username",$username);

        $stmt->execute();

        $res = $stmt->fetch(PDO::FETCH_ASSOC);


        return !empty($res);
    }

    /**
     * 密码加密算法
     * @param $string
     * @param string $key
     * @return string
     */
    private function _md5($string,$key="imooc"){
        return md5($string.$key);
    }
}