<?php
/**
 * Created by PhpStorm.
 * User: 洋
 * Date: 2016/9/9
 * Time: 17:12
 */
namespace Admin\Controller;
use Think\Controller;

class RegisterController extends Controller
{
    public function register()
    {
        if(IS_POST) {

        } else {
            $this->display();
        }
    }
}