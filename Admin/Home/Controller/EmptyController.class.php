<?php
namespace Home\Controller;

/**
 * EmptyController
 * 空控制器
 */
class EmptyController extends CommonController {
    /**
     * 重定向操作
     * @return
     */
    public function index() {
        $this->error('亲，您访问的页面不存在！');
    }
}
