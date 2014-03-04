<?php
namespace Home\Controller;

class InstallController extends CommonController {
    public function index() {
        $this->display();
    }

    public function create() {
        $this->display('installing');
    }
}
