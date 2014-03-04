<?php
namespace Home\Controller;

use Think\Controller;

class CommonController extends Controller {
    public function _initialize() {
        header('Content-Type: text/html; charset=UTF-8');
    }
}
