<?php
/**
 * Created by PhpStorm.
 * User: 洋
 * Date: 2016/9/6
 * Time: 18:21
 */
//建立一个中间控制器
namespace Admin\Controller;
use Think\Controller;

class CommenController extends Controller
{
    public function __construct()
    {
        //父类是个抽象类，所以先给他实例化
        parent::__construct();
        $uid = session('uid');
        if (empty($uid)) {
            #空就跳转到登陆页,这里要用到J帅跳转，用U方法会出现画中画
            $url = U('Public/index');
            $script = "<script>top.location.href = '$url' ;</script>";
            echo $script;exit;
        }
    }
}