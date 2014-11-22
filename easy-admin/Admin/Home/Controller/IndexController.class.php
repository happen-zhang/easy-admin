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
        $gd = '不支持';
        if (function_exists('gd_info')) {
            $gd = gd_info();
            $gd = $gd['GD Version'];
        }

        $hostport = $_SERVER['SERVER_NAME']
                    ."($_SERVER[SERVER_ADDR]:$_SERVER[SERVER_PORT])";
        $mysql = function_exists('mysql_close') ? mysql_get_client_info()
                                                : '不支持';
        $info = array(
            'system' => get_system(),
            'hostport' => $hostport,
            'server' => $_SERVER['SERVER_SOFTWARE'],
            'php_env' => php_sapi_name(),
            'app_dir' => WEB_ROOT,
            'mysql' => $mysql,
            'gd' => $gd,
            'upload_size' => ini_get('upload_max_filesize'),
            'exec_time' => ini_get('max_execution_time') . '秒',
            'disk_free' => round((@disk_free_space(".")/(1024 * 1024)),2).'M',
            'server_time' => date("Y-n-j H:i:s"),
            'beijing_time' => gmdate("Y-n-j H:i:s", time() + 8 * 3600),
            'reg_gbl' => get_cfg_var("register_globals") == '1'? 'ON' : 'OFF',
            'quotes_gpc' => (1 === get_magic_quotes_gpc()) ? 'YES' : 'NO',
            'quotes_runtime' => (1===get_magic_quotes_runtime()) ?'YES' : 'NO',
            'fopen' => ini_get('allow_url_fopen') ? '支持' : '不支持'
        );

        $this->assign('info', $info);
        $this->display();
    }

    /**
     * 编辑个人密码
     * @return
     */
    public function editPassword() {
        $admin = $_SESSION['current_account'];

        $this->assign('admin', $admin);
        $this->display('edit_password');
    }

    /**
     * 更新个人密码
     * @return
     */
    public function updatePassword() {
        if (!isset($_POST['admin'])) {
            return $this->errorReturn('无效的操作！');
        }

        $admin = $_SESSION['current_account'];
        $admin['password'] = $_POST['admin']['password'];
        $admin['cfm_password'] = $_POST['admin']['cfm_password'];

        $result = D('Admin', 'Service')->update($admin);
        if (!$result['status']) {
            return $this->errorReturn($result['data']['error']);
        }

        return $this->successReturn('修改密码成功！');
    }

    /**
     * 编辑站点信息
     * @return
     */
    public function siteEdit() {
        $title = C('SITE_TITLE');
        $keyword = C('SITE_KEYWORD');
        $description = C('SITE_DESCRIPTION');

        $this->assign('title', $title);
        $this->assign('keyword', $keyword);
        $this->assign('description', $description);
        $this->display('site_edit');
    }

    /**
     * 更新站点信息
     * @return
     */
    public function siteUpdate() {
        if (!isset($_POST['site'])) {
            return $this->errorReturn('无效的操作！');
        }

        $confName = 'system_config';

        $conf = fast_cache($confName, '', C('COMMON_CONF_PATH'));
        $conf['SITE_TITLE'] = $_POST['site']['title'];
        $conf['SITE_KEYWORD'] = $_POST['site']['keyword'];
        $conf['SITE_DESCRIPTION'] = $_POST['site']['description'];

        if (false === fast_cache($confName, $conf, C('COMMON_CONF_PATH'))) {
            return $this->errorReturn('站点信息更新失败！');
        }

        return $this->successReturn('站点信息更新成功！');
    }
}
