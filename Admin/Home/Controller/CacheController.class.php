<?php
namespace Home\Controller;

/**
 * CacheController
 * 缓存管理
 */
class CacheController extends CommonController {
    /**
     * 缓存信息
     * @return
     */
    public function index() {
        $caches = array(
            'cache' => RUNTIME_PATH . 'Cache/',
            "data" => RUNTIME_PATH . 'Data/',
            "logs" => RUNTIME_PATH . 'Logs/',
            "temp" => RUNTIME_PATH . 'Temp/',
            "runtime" => RUNTIME_PATH . '~runtime.php/',
            "MinFiles" => WEB_ROOT . 'Cache/MinFiles/'
        );

        $this->assign('caches', $caches);
        $this->display();
    }

    /**
     * 删除缓存
     * @return
     */
    public function delete() {
        if (empty($_POST['caches'])) {
            return $this->errorReturn('请选择需要删除的缓存文件！');
        }

        foreach ($_POST['caches'] as $cache) {
            if (isset($cache)) {
                del_dir_or_file($cache);
            }
        }

        return $this->successReturn('成功删除缓存文件！');
    }
}
