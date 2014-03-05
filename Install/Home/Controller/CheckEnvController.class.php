<?php
namespace Home\Controller;

/**
 * CheckEnvController
 */
class CheckEnvController extends CommonController {
    public function index(){
        // 得到当前系统的信息
        $systemInfo = $this->getSystemInfo();
        // 得到PHP版本
        $phpVersion = $this->getPHPVersion();
        // 得到服务器软件
        $serverInfo = $this->getServerInfo();
        // Mysql信息
        $mysqlInfo = $this->getMysqlInfo();
        // 文件上传支持
        $uploadInfo = $this->getUploadInfo();
        // session支持
        $sessionInfo = $this->getSessionInfo();
        // GD库支持
        $gdInfo = $this->getGDInfo();

        $directories = C('WRITABLE_DIRECTORIES');
        $directoriesState = $this->getDirctoriesState($directories);

        $this->assign('system_info', $systemInfo);
        $this->assign('php_version', $phpVersion);
        $this->assign('server_info', $serverInfo);
        $this->assign('mysql_info', $mysqlInfo);
        $this->assign('upload_info', $uploadInfo);
        $this->assign('session_info', $sessionInfo);
        $this->assign('gd_info', $gdInfo);
        $this->assign('directories_state', $directoriesState);

        $this->display();
    }

    /**
     * 检查目录可读、可写权限
     * @param  array  $directories
     * @return array
     */
    private function getDirctoriesState(array $directories) {
    	$dirStates = array();

        foreach ($directories as $key => $dir) {
        	$fullDirPath = WEB_ROOT . $dir;
            create_dir($fullDirPath);

            $dirStates[$key]['dir_name'] = $dir;
            // 测试可写
            if (is_writable($fullDirPath)) {
                $dirStates[$key]['writable'] = current_state_support('可写'); 
            } else {
                $dirStates[$key]['writable'] = current_state_unsupport('不可写');
            }

            // 测试可读
            if (is_readable($fullDirPath)) {
                $dirStates[$key]['readable'] = current_state_support('可读'); 
            } else {
                $dirStates[$key]['readable'] = current_state_unsupport('不可读');
            }
        }

        return $dirStates;
    }

    private function getSystemInfo() {
        return current_state_support(get_system());
    }

    private function getPHPVersion() {
        return current_state_support(phpversion());
    }

    private function getServerInfo() {
        return current_state_support($_SERVER['SERVER_SOFTWARE']);
    }

    private function getMysqlInfo() {
        if (function_exists('mysql_connect')) {
            return current_state_support('已安装');
        }

        return current_state_unsupport('出现错误');
    }

    private function getUploadInfo() {
        if (ini_get('file_uploads')) {
            return current_state_support(ini_get('upload_max_filesize'));
        }

        return current_state_unsupport('禁止上传');
    }

    private function getSessionInfo() {
        if (function_exists('session_start')) {
            return current_state_support('支持');
        }

        return current_state_support('不支持');
    }

    private function getGDInfo() {
        if (function_exists('gd_info')) {
            $gd = gd_info();
            return current_state_support($gd['GD Version']);
        }
        
        return current_state_unsupport('不支持');
    }
}
