<?php
namespace Home\Service;

/**
 * NodeService
 */
class NodeService extends CommonService {
    /**
     * 节点类型
     * @var array
     */
    private $NODE_TYPE = array(
        1 => '应用（GROUP）',
        2 => '模块（MODULE）',
        3 => '操作（ACTION）'
    );

    /**
     * 得到带又层级的node数据
     * @return array
     */
    public function getNodes() {
        $category = new \Org\Util\Category($this->getModelName(),
                                           array('id', 'pid','title'));
        return $category->getList();
    }

    /**
     * 得到节点的类型
     * @param  int    $type 节点的类型
     * @return string
     */
    public function getNodeType($type) {
        return $this->NODE_TYPE[$type];
    }

    protected function getModelName() {
        return 'Node';
    }
}
