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
            'Admincache' => RUNTIME_PATH . 'Cache/',
            "Admindata" => RUNTIME_PATH . 'Data/',
            "Adminlogs" => RUNTIME_PATH . 'Logs/',
            "Admintemp" => RUNTIME_PATH . 'Temp/',
            "Adminruntime" => RUNTIME_PATH . '~runtime.php/',
            'Homecache' => WEB_ROOT . 'Cache/Runtime/Home/Cache/',
            "Homedata" => WEB_ROOT . 'Cache/Runtime/Home/Data/',
            "Homelogs" => WEB_ROOT . 'Cache/Runtime/Home/Logs/',
            "Hometemp" => WEB_ROOT . 'Cache/Runtime/Home/Temp/',
            "Homeruntime" => WEB_ROOT . 'Cache/Runtime/Home/~runtime.php/',
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
