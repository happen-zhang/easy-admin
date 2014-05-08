<?php
namespace Home\Controller;

/**
 * NodesController
 * 节点信息
 */
class NodesController extends CommonController {
    /**
     * 节点列表
     * @return
     */
    public function index() {
        $nodeService = D('Node', 'Service');
        $nodes = $nodeService->getNodes();

        foreach ($nodes as $key => $node) {
            $nodes[$key]['type'] = $nodeService->getNodeType($node['level']);
        }

        $this->assign('nodes', $nodes);
        $this->assign('rows_count', count($nodes));
        $this->display();
    }
}
