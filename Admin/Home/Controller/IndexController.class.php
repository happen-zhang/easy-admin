<?php
namespace Home\Controller;

/**
 * IndexController
 * 系统信息管理
 */
class IndexController extends CommonController {
    /**
     * 网站，服务器基本信息
     * @return
     */
    public function index(){
        $this->display();
    }
}
