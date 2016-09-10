<?php
namespace Admin\Controller;
use Think\Controller;
use Think\Image;
use Think\Page;
use Think\Upload;

//继承Controller
class ArticleController extends CommenController
{
   public function add()
   {
       if(IS_POST){
           $model = D('Article');
           $rst = $model -> create();
           #dump($model -> getError());die;
           #判断数据对象的返回结果
           if(!$rst){
               $this -> error($model -> getError(),U('add'),3);exit;
           }
           //接受表单提交的值
           $post = I('post.');
           $file = $_FILES['file'];
           //判断文件是否上传成功
           if($file['size'] > 0){
               #设置文件保存路径，并通过一个数组传给文件上传类
               $cfg = array(
                   'rootPath' => WORKING_PATH . UPLOAD_ROOT_PATH,
               );
               #实例化上传类操作上传单个文件
               $upload = new Upload($cfg);
               $info = $upload -> uploadOne($file);//调用上传类的方法成功上传文件返回的信息
               //dump($info);判断上传文件的成功与否
                if($info){
                    #表中的hasfile 字段
                    $post['hasfile'] = 1;
                    #表中的filename 字段
                    $post['filename'] = $info['savename'];
                    #表中的filepath字段(相对路径)
                    $post['filepath'] = UPLOAD_ROOT_PATH . $info['savepath'] . $info['savename'];
                    #缩略图制作，图片类
                    #picture字段,由于可以在Img标签中显示出来，故用相对路径
                    $post['picture'] =  UPLOAD_ROOT_PATH .$info['savepath'] . $info['savename'];
                    #实例化图片类
                    $image = new Image();
                    #打开图片路径，这里用的路径是绝对路径，在后台用的路径都是绝对路径
                    $image -> open(WORKING_PATH . $post['picture']);
                    #制作缩略图
                    $image -> thumb(90,90);
                    #保存缩略图
                    $image -> save(WORKING_PATH . UPLOAD_ROOT_PATH . $info['savepath'] . 'thumb_' . $info['savename']);
                    #thumb字段相对路径
                    $post['thumb'] = UPLOAD_ROOT_PATH . $info['savepath'] .'thumb_'.$info['savename'];
                }
           }
           #添加时间字段,用户字段
           $post['user_id'] = session('uid');
           $post['addtime'] = time();
           //实例化文章类
           $res = $model -> add($post);
           if ($res) {
               $this -> success('文章添加成功',U('showList'),3);
           } else {
               $this -> error('文章添加失败',U('add'),3);
           }
       } else{
           //添加页的分类
           //1,实例化文章类表
           $model = M('Category');
           $rs = $model -> select();
           //实现无限极分类
           $rs = getTree($rs);
           //向模板传递值
           $this -> assign('rs',$rs);
           //显示模板
           $this -> display();
       }
   }
    //添加文章更新页
    public function edit()
    {
        if(IS_POST){
        //实例化类
            $model = M('Article');
            //接受表单传值
            $post = I('post.');
            $id = I('post.id');
            //dump($id);
            //dump($post);die;
            //如果有文件上传
            $file = $_FILES['file'];
            if ($file['size'] > 0){
                //定义一个文件上传存储的路径
                $cfg = array(
                    'rootPath' => WORKING_PATH . UPLOAD_ROOT_PATH,
                );
                //实例化上传类
                $upload = new Upload($cfg);
                //调用上传类的方法
                $info = $upload -> uploadOne($file);
                //dump($info);
                //判断返回的数组
                if ($info) {
                    //hasfile字段
                    $post['hasfile'] = 1;
                    //制作缩略图
                    $image = new Image();
                    //picture字段.相对路径
                    $post['picture'] = UPLOAD_ROOT_PATH .$info['path'] . $info['savename'];
                    //实例化图片后，那就打开图片
                    $image -> open(WORKING_PATH . $post['picture']);
                    //制作缩略图
                    $image -> thumb(90,90);
                    //保存图片
                    $image -> save(WORKING_PATH . UPLOAD_ROOT_PATH . $info['savepath'] .$info['savename']);
                    //thumb字段,相对路径
                    $post['thumb'] = UPLOAD_ROOT_PATH . $info['savepath'] .$info['savename'];
                    //filename字段
                    $post['filename'] = $info['savename'];
                    $post['filepath'] = UPLOAD_ROOT_PATH . $info['savepath'] . $info['savename'];
                }
            }
                   $post['addtime'] = time();
            $res = $model -> where('id=' . $id) -> save($post);
            if ($res !== false) {
                $this -> success('更新成功',U('showlist'),3);
            } else {
                $this -> errpr('更新失败',U('edit',array('id' => $id)),3);
            }
        } else{
            //接受修改的传参
            $id = I('get.id');
            //显示文章类型分类
            $model = M('Category');
            $rs = $model -> select();
            //实现无线及分类
            $rs = getTree($rs);
            //根据id的值获取值并显示
            $article = M('Article');
            $res = $article -> find($id);
            $this -> assign('rs',$rs);
            $this -> assign('res',$res);
            $this -> display();
        }
    }
    //文章显示页
    public function showList()
    {
        //实例化模型类
        $model = M('Article');
        //构建查询sql语句
        //$sql="SELECT t1.*,t2.name AS category_name FROM tp_article AS t1,tp_category AS t2 WHERE t1.category_id = t2.id";
        //实现分页效果
        //设置一些分页参数

        ////////////
        //查询数据
        if(IS_POST){
           $str = I('post.keywords');
            session('str',$str);
            //dump($_SESSION);
           // $sql ="SELECT t1.*,t2.name AS category_name FROM tp_article AS t1,tp_category AS t2 WHERE ( t1.category_id = t2.id and t1.title like '%宝宝%' )LIMIT 0,2";
            //如果提交了模糊查询的字段应该统计出模糊查询的个数
            //
            $count = $model -> where('tp_article.title like'. "'%$str%'")-> count() ;
            $res = $model -> field('t1.*,t2.name AS category_name') ->table('tp_article AS t1,tp_category AS t2') -> where('t1.category_id = t2.id and t1.title like' . "'%$str%'")   ->select();

        } else{
            $count = $model -> count();//总记录数
            //实例化分页类
            $page = new Page($count,4);
        $res = $model -> field('t1.*,t2.name AS category_name') ->table('tp_article AS t1,tp_category AS t2') -> where('t1.category_id = t2.id') -> limit($page -> firstRow,$page -> listRows) ->select();}
        $page = new Page($count,4);
        $page -> lastSuffix = false;
        $page -> rollPage = 3;
        $page -> setConfig('prev','上一页');
        $page -> setConfig('next','下一页');
        $page -> setConfig('first','首页');
        $page -> setConfig('last','末页');
        //调用显示分页方法
        $show = $page -> show();
        //dump($res);
        //传递数据
        $this -> assign('show',$show);
        $this -> assign('count',$count);
        $this -> assign('res',$res);
        $this -> display();
    }
    //删除功能
    public function del(){
        $id = I('get.id');
        //实例化类
        $model = M('Article');
        //调用方法
        $res = $model -> delete($id);
        if ($res) {
            $this -> success('删除成功',U('showlist'),3);
        } else {
            $this -> error('删除失败',U('showlist'),3);
        }
    }
    //添加一个获取点击数的方法
    public function hits()
    {
        $id = I('get.id');
        //dump($id);die();
        $model = M('Article');
        //统计字段加1
        $model -> where('id='. $id) -> setInc('hits');
    }
    //layer获取内容
    public function getContent()
    {
        $id = I('get.id');
        $model = M('Article');
        $data = $model -> find($id);
        echo $data['content'];
        $this -> hits();
    }
    
}