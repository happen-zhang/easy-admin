<?php

namespace Home\Logic;

/**
 * ModelLogic
 */
class ModelLogic extends CommonLogic {
    /**
     * 写出菜单配置
     * @param  array $menu 菜单数组
     * @return mixed
     */
    public function writeMenu(array $menu) {
        // 删除缓存
        del_dir_or_file(RUNTIME_PATH . '~runtime.php');
        return fast_cache('model_menu', $menu, APP_PATH . '/Common/Conf/');
    }

    /**
     * 得到菜单
     * @return mixed
     */
    public function getMenu() {
        $menu = fast_cache('model_menu', '', APP_PATH . '/Common/Conf/');

        if (!is_array($menu)) {
            return array();
        }

        return $menu;
    }

    /**
     * 添加菜单项
     * @param  array $menuItem 需要加入到菜单的菜单项
     * @return array
     */
    public function addMenuItem(array $menuItem) {
        if (is_null($menuItem)) {
            return false;
        }

        $menu = array_merge($this->getMenu(), $menuItem);
        $this->writeMenu($menu);
        return $menu;
    }

    /**
     * 删除菜单项
     * @param  array  $ctrlName 需要删除的菜单项名称
     * @return mixed
     */
    public function delMenuItem($ctrlName) {
        $menu = $this->getMenu();
        unset($menu[$ctrlName]);

        $this->writeMenu($menu);
        return $menu;
    }

    /**
     * 生成菜单项
     * @param  string $itemName  菜单名称
     * @param  string $ctrlName 模型名称
     * @return  array            菜单数组
     */
    public function genMenuItem($itemName, $ctrlName) {
        $menu[$ctrlName] = array(
            'name' => $itemName . '管理',
            'target' => "{$ctrlName}/index",
            'sub_menu' => array(
                // CURD菜单
                array('item' => array("{$ctrlName}/index" =>$itemName .'管理')),
                array('item' => array("{$ctrlName}/add" => '添加' .$itemName)),
                array('item' => array("{$ctrlName}/show" => $itemName . '信息'),
                      'hidden' => true),
                array('item' => array("{$ctrlName}/edit" => '编辑' .$itemName),
                      'hidden' => true)
            )
        );

        return $menu;
    }
}
