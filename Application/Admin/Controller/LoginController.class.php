<?php
/**
 * Created by PhpStorm.
 * User: 洋
 * Date: 2016/9/9
 * Time: 15:52
 */
namespace Admin\Controller;
use Think\Controller;
use Think\Verify;

class LoginController extends Controller
{
    //设置验证码
    public function verify()
    {
        //设置验证码参数
        $cfg = array(
            'useImgBg'  =>  false,           // 使用背景图片
            'fontSize'  =>  14,              // 验证码字体大小(px)
            'useCurve'  =>  false,            // 是否画混淆曲线
            'useNoise'  =>  false,            // 是否添加杂点
            'imageH'    =>  34,               // 验证码图片高度
            'imageW'    =>  106,               // 验证码图片宽度
            'length'    =>  4,               // 验证码位数
            'fontttf'   =>  '4.ttf',              // 验证码字体，不设置随机获取
        );
        //实例化验证码图片
        $verify = new Verify($cfg);
        //开启ob_clen
//        ob_clean();
        #调用验证码图片
        $verify -> entry();
    }
    public function test(){
        echo 'test';
    }

    //登录页的判断
    public function index()
    {
        if(IS_POST) {
            $post = I('post.');
            $captch = $post['captch'];
            $verify = new Verify();
            $res = $verify -> check($captch);
            if($res !== true)
            {
                $this -> error('验证码不正确，请重新输入',U('index'),3);
            }
            //接下来判断用户名，在这之前首先将数组内验证码销毁
             unset($post['captch']);
            //实例化User表
            $model = M('User');
            //查询用户名和密码字段
            $result = $model -> where($post) -> find();
            if ($result) {
                $this -> success('登录成功',U('Article/index'),3);
            } else {
                $this -> error('用户名或密码有误',U('index'),3);
            }
        } else {
            $this -> display();
        }

    }

}