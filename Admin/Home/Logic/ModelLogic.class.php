<?php

namespace Home\Logic;

/**
 * ModelLogic
 */
class ModelLogic extends CommonLogic {
    /**
     * 写出菜单配置
     * @param  array $menu 菜单数组
     * @return
     */
    public function writeMenu(array $menu) {
        $modelMenu = fast_cache('model_menu', '', APP_PATH . '/Common/Conf/');

        if (false === $modelMenu) {
            $modelMenu = array();
        }

        // 删除缓存
        del_dir_or_file(RUNTIME_PATH . '~runtime.php');
        return fast_cache('model_menu',
                          array_merge($modelMenu, $menu),
                          APP_PATH . '/Common/Conf/');
    }

    /**
     * 生成菜单
     * @param  string $menuName  菜单名称
     * @param  string $modelName 模型名称
     * @return  array            菜单数组
     */
    public function generateMenu($menuName, $modelName) {
        $menu[$modelName] = array(
            'name' => $menuName . '管理',
            'target' => "{$modelName}/index",
            'sub_menu' => array(
                // CURD菜单
                array('item' => array("{$modelName}/index" =>$menuName.'管理')),
                array('item' => array("{$modelName}/add" => '添加' .$menuName)),
                array('item' => array("{$modelName}/show" => $menuName .'信息'),
                      'hidden' => true),
                array('item' => array("{$modelName}/edit" => '编辑' .$menuName),
                      'hidden' => true)
            )
        );

        return $menu;
    }
}
