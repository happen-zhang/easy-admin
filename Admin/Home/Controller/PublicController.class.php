<?php
namespace Home\Controller;

/**
 * PublicController
 * 公开页面访问接口
 */
class PublicController extends CommonController {
    /**
     * 管理员登录页
     * @return
     */
    public function index() {
        layout(false);
        $this->display();
    }

    /**
     * 管理员登录
     * @return
     */
    public function login() {

    }

    /**
     * 管理员登出
     * @return
     */
    public function logout() {

    }

    /**
     * 验证码图片
     * @return
     */
    public function verifyCode() {
        $config = array(
            'imageW' => 60,
            'imageH' => 30,
            'fontSize' => 8,
            'length' => 4,
            'useNoise' => false,
            'codeSet' => '0123456789'
        );
        create_verify_code($config);
    }
}
