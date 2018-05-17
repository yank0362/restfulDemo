<?php
/**
 * Created by PhpStorm.
 * User: Connie
 * Date: 2018/5/14
 * Time: 11:48
 */

require_once __DIR__."/ErrorCode.php";
class Article
{
    private $_db;

    /**
     * Article constructor.
     * @param $_db
     */
    public function __construct($db)
    {
        $this->_db = $db;
    }


    /**
     * 创建文章
     * @param $title
     * @param $content
     * @param $userId
     * @return array
     * @throws Exception
     */
    public function create($title,$content,$userId){
        if(empty($title)){
            throw new Exception("文章标题不能为空！",ErrorCode::ARTICLE_TITLE_CANNOT_EMPTY);
        }

        if(empty($content)){
            throw new Exception("文章内容不能为空！",ErrorCode::ARTICLE_CONTENT_CANNOT_EMPTY);
        }

        $sql = 'insert into `article` (title,content,user_id,created_at) values (:title,:content,:userId,:created_at)';
        $createdAt = Date("Y-m-d H:i:s");
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(":title",$title);
        $stmt->bindParam(":content",$content);
        $stmt->bindParam(":userId",$userId);
        $stmt->bindParam(":created_at",$createdAt);
        if(!$stmt->execute()){
            throw new Exception("文章创建失败！",ErrorCode::ARTICLE_CREATE_FAIL);
        }

        return [
            "articleId"=>$this->_db->lastInsertId(),
            "title"=>$title,
            "content"=>$content,
            "userId"=>$userId,
            "createdAt"=>$createdAt,
        ];


    }

    /**
     * 编辑文章
     * @param $articleId
     * @param $title
     * @param $content
     * @param $userId
     * @return array
     * @throws Exception
     */
    public function edit($articleId,$title,$content,$userId){
        //文章编辑之前首先需要获取文章
        $article = $this->view($articleId);

        if($userId!==$article['user_id']){
            throw new Exception("您无权编辑该文章！",ErrorCode::PERMISSION_DENIED);
        }

        $title = empty($title)?$article['title']:$title;//如果为空则不变
        $content = empty($content)?$article['content']:$content;//如果为空则不变

        $sql = 'update `article` set title=:title,content=:content where article_id =:articleId';
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(":title",$title);
        $stmt->bindParam(":content",$content);
        $stmt->bindParam(":articleId",$articleId);
        if(!$stmt->execute()){
            throw new Exception("文章更新失败！",ErrorCode::ARTICLE_EDIT_FAIL);
        }

        return [
            'articleId'=>$articleId,
            'title'=>$title,
            'content'=>$content,
            'userId'=>$userId,
            'createdAt'=>$article['created_at'],
        ];

    }

    /**
     * 文章删除
     * @param $articleId
     * @param $userId
     * @return bool
     * @throws Exception
     */
    public function delete($articleId,$userId){
        $article = $this->view($articleId);
        if($article['user_id']!==$userId){
            throw new Exception("无权访问",ErrorCode::PERMISSION_DENIED);
        }
        $sql = 'delete from `article` where article_id=:articleId and user_id=:userId';
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(":articleId",$articleId);
        $stmt->bindParam(":userId",$userId);
        if(!$stmt->execute()){
            throw new Exception("文章删除失败!",ErrorCode::ARTICLE_DELETE_FAIL);
        }
        return true;
    }

    /**
     * 获取文章列表
     * @param $userId
     * @param int $page
     * @param int $size
     * @return mixed
     * @throws Exception
     */
    public function getList($userId,$page = 1,$size = 10){

        if($size>100){
            throw new Exception("分页大小超过100",ErrorCode::PAGESIZE_TOO_BIG);
        }
        $sql = 'SELECT * FROM `article` WHERE `user_id`=:userId limit :startX,:endX';

        $startx = ($page-1)*$size;
        $startx = $startx<0?0:$startx;

        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':userId',$userId);
        $stmt->bindParam(':startX',$startx);
        $stmt->bindParam(':endX',$size);

        $stmt->execute();
        $article = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $article;

    }

    /**
     * 获取一篇文章
     * @param $articleId
     * @return mixed
     * @throws Exception
     */
    public function view($articleId){
        if(empty($articleId)){
            throw new Exception("文章ID不能为空",ErrorCode::ARTICLE_ID_CANNOT_EMPTY);
        }
        $sql = "select * from `article` where article_id=:articleId";

        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(":articleId",$articleId);
        $stmt->execute();
        $article = $stmt->fetch(PDO::FETCH_ASSOC);

        if(empty($article)){
            throw new Exception("文章不存在",ErrorCode::ARTICLE_NOT_FOUND);
        }

        return $article;
    }

}
