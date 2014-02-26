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

        // 分配菜单
        $this->assignMenu();
    }

    /**
     * 分配菜单
     * @return
     */
    protected function assignMenu() {
        $menu = $this->getMenu();

        $this->assign('main_menu', $menu['main_menu']);
        $this->assign('sub_menu', $menu['sub_menu']);
    }

    /**
     * 得到菜单
     * @return array
     */
    protected function getMenu() {
        $menu = C('MENU');

        // 主菜单
        $mainMenu = array();
        foreach ($menu as $key => $menuItem) {
            $mainMenu[$key]['name'] = $menuItem['name'];
            $mainMenu[$key]['target'] = $menuItem['target'];
        }
        // 子菜单
        $subMenu = $menu[CONTROLLER_NAME]['sub_menu'];

        unset($menu);
        return array(
            'main_menu' => $mainMenu,
            'sub_menu' => $subMenu
        );
    }
}
