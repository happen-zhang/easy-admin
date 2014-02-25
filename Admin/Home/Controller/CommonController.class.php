<?php
namespace Home\Controller;

use Think\Controller;

/**
 * CommonController
 * 通用控制器
 */
class CommonController extends Controller {
    /**
    * 全局初始化
    * @return
    */
    public function _initialize() {
        // utf-8编码
        header('Content-Type: text/html; charset=utf-8');
    }
}
