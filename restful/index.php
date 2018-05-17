<?php
/**
 * Created by PhpStorm.
 * User: Connie
 * Date: 2018/5/15
 * Time: 10:08
 */
require_once __DIR__."/../lib/User.php";
require_once __DIR__."/../lib/Article.php";
$pdo = require_once __DIR__."/../lib/db.php";
class Restful
{
    private $_user;
    private $_article;
    private $_requestMethod;//请求方法
    private $_resourceName;//资源名称
    private $_id;//资源ID
    private $_allowResources = ['users','articles'];//允许请求的资源
    private $_allowRequestMethods = ['GET','POST','PUT','DELETE','OPTIONS'];//允许请求的方法
    /**
     * @var array 常用状态码
     */
    private $_statusCodes = [
        200 => 'Ok',
        204 => 'No Content',
        400 => 'Bad request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        500 => 'Server Internal Error'
    ];

    /**
     * Restful constructor.
     * @param $_user
     * @param $_article
     */
    public function __construct($_user, $_article)
    {
        $this->_user = $_user;
        $this->_article = $_article;
    }

    /**
     * 入口方法
     */
    public function run(){

        try{
            $this->setupRequestMethod();
            $this->setupResources();
            if($this->_resourceName == 'users'){
                $this->_json($this->_handleUsers());
            }else{
                $this->_json($this->_handleArticles());
            }

        }catch (Exception $e){

            $this->_json(['error'=>$e->getMessage()], $e->getCode());
        }

    }


    /**
     * 初始化请求方法
     * @throws Exception
     */
    private function setupRequestMethod()
    {
        $this->_requestMethod = $_SERVER['REQUEST_METHOD'];
        if(!in_array($this->_requestMethod,$this->_allowRequestMethods)){
            throw new Exception("请求方法不被允许",405);
        }

    }

    /**
     * 初始化请求资源
     * @throws Exception
     */
    private function setupResources()
    {
        $path = $_SERVER['PATH_INFO'];
        $params = explode('/',$path);
        $this->_resourceName = $params[1];
        if(!in_array($this->_resourceName,$this->_allowResources)){
            throw new Exception("请求资源不被允许",400);
        }
        if(!empty($params[2])){
            $this->_id = $params[2];
        }
    }


    /**
     * 输入JSON
     * @param $array
     */
    private function _json($array,$code=0)
    {
        if($code ==0 && $array === null){
            $code = 204;
        }

        if($code ==0 && $array != null){
            $code = 200;
        }



        header("HTTP/1.1 {$code} {$this->_statusCodes[$code]}");
        header("Content-type=application/json;charset=utf-8");
        echo json_encode($array,JSON_UNESCAPED_UNICODE);
        exit();
    }

    /**
     * 用户处理
     * @return mixed
     * @throws Exception
     */
    private function _handleUsers()
    {
        if($this->_requestMethod != 'POST'){
            throw new Exception("请求方法不被允许",405);
        }

        $body = $this->_getBodyParams();

        if(empty($body['username'])){
            throw new Exception("用户名不能为空",400);
        }

        if(empty($body['password'])){
            throw new Exception("密码不能为空",400);
        }

        return $this->_user->register($body['username'],$body['password']);

    }

    /**
     * 文章处理
     */
    private function _handleArticles()
    {
        switch ($this->_requestMethod){
            case 'POST':
                return $this->_handleArticleCreate();
            case 'PUT':
                return $this->_handleArticleEdit();
            case 'DELETE':
                return $this->_handleArticleDelete();
            case 'GET':
                if(empty($this->_id)){
                    return $this->_handleArticleList();
                }else{
                    return $this->_handleArticleView();
                }
            default:
                throw new Exception("请求方式不被允许",405);
        }

    }

    /**
     * 获取请求体参数
     * @return mixed
     * @throws Exception
     */
    private function _getBodyParams()
    {
        $raw = file_get_contents("php://input");
        if(empty($raw)){
            throw new Exception("请求参数错误",400);
        }

        return json_decode($raw,true);
    }

    /**
     * 文章创建
     * @return mixed
     * @throws Exception
     */
    private function _handleArticleCreate()
    {
        $body = $this->_getBodyParams();
        if(empty($body['title'])){
            throw new Exception("文章标题不能为空",400);
        }

        if(empty($body['content'])){
            throw new Exception("文章内容不能为空",400);
        }


        if(empty($_SERVER['PHP_AUTH_USER'])){
            throw new Exception("用户名不能为空",400);
        }

        if(empty($_SERVER['PHP_AUTH_PW'])){
            throw new Exception("密码不能为空",400);
        }

        $user = $this->_userLogin($_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW']);


        try{
            $article = $this->_article->create($body['title'],$body['content'],$user['user_id']);
            return $article;
        }catch (Exception $e){
            if(!in_array($e->getCode(),[
                ErrorCode::ARTICLE_TITLE_CANNOT_EMPTY,
                ErrorCode::ARTICLE_CONTENT_CANNOT_EMPTY,
            ])){
                throw new Exception($e->getMessage(),400);
            }
            throw new Exception($e->getMessage(),500);
        }
    }

    /**
     * 文章编辑
     * @return mixed
     * @throws Exception
     */
    private function _handleArticleEdit()
    {
        try{
            if(!empty($_SERVER['PHP_AUTH_USER'])&&!empty($_SERVER['PHP_AUTH_PW'])){
                $user = $this->_userLogin($_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW']);
                $aricle = $this->_article->view($this->_id);

                if($aricle['user_id']!=$user['user_id']){
                    throw new Exception("您无权编辑",403);
                }
                $body = $this->_getBodyParams();

                $title = empty($body['title'])?$aricle['title']:$body['title'];
                $content = empty($body['content'])?$aricle['content']:$body['content'];
                if($title == $aricle['title'] && $content == $aricle['content']){

                    return $aricle;
                }

                return $this->_article->edit($aricle['article_id'],$title,$content,$user['user_id']);
            }


        }catch (Exception $e){

            if($e->getCode()<100){
                if($e->getCode()==ErrorCode::ARTICLE_NOT_FOUND){
                    throw new Exception($e->getMessage(),404);
                }else{
                    throw new Exception($e->getMessage(),400);
                }

            }else{
                throw $e;
            }



        }


    }

    /**
     * 文章删除
     * @return null
     * @throws Exception
     */
    private function _handleArticleDelete()
    {

        try{
            if(!empty($_SERVER['PHP_AUTH_USER'])&&!empty($_SERVER['PHP_AUTH_PW'])){
                $user = $this->_userLogin($_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW']);
                $aricle = $this->_article->view($this->_id);

                if($aricle['user_id']!=$user['user_id']){
                    throw new Exception("您无权删除",403);
                }
                $this->_article->delete($aricle['article_id'],$user['user_id']);
                return null;
            }


        }catch (Exception $e){

            if($e->getCode()<100){
                if($e->getCode()==ErrorCode::ARTICLE_NOT_FOUND){
                    throw new Exception($e->getMessage(),404);
                }else{
                    throw new Exception($e->getMessage(),400);
                }

            }else{
                throw $e;
            }



        }


    }

    /**
     * 请求文章列表
     * @return mixed
     * @throws Exception
     */
    private function _handleArticleList()
    {
        if(!empty($_SERVER['PHP_AUTH_USER'])&&!empty($_SERVER['PHP_AUTH_PW'])){
            $user = $this->_userLogin($_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW']);

            $page = isset($_GET['page'])?$_GET['page']:1;
            $size = isset($_GET['size'])?$_GET['size']:10;
            if($size>100){
                throw new Exception("分页大小最大100",400);
            }
            return $this->_article->getList($user['user_id'],$page,$size);
        }


    }

    private function _handleArticleView()
    {
        try{
            return $this->_article->view($this->_id);
        }catch (Exception $e){
            if($e->getCode()==ErrorCode::ARTICLE_NOT_FOUND){
                throw new Exception($e->getMessage(),404);
            }else{
                throw new Exception($e->getMessage(),500);
            }
        }
    }

    /**
     * 用户登录
     * @param $PHP_AUTH_USER
     * @param $PHP_AUTH_PW
     * @return mixed
     * @throws Exception
     */
    private function _userLogin($PHP_AUTH_USER, $PHP_AUTH_PW)
    {
        try{
            return $this->_user->login($PHP_AUTH_USER,$PHP_AUTH_PW);

        }catch (Exception $e){
            if(in_array($e->getCode(),[
                ErrorCode::USERNAME_CANNOT_EMPTY,
                ErrorCode::PASSWORD_CANNOT_EMPTY,
                ErrorCode::USERNAME_OR_PASSWORD_INVALID,
                ])){
                throw new Exception($e->getMessage(),401);
            }
            throw new Exception($e->getMessage(),500);
        }

    }

}

$user = new User($pdo);
$article = new Article($pdo);
$restful = new Restful($user,$article);
$restful->run();

