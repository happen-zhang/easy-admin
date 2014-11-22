<?php
// +----------------------------------------------------------------------
// | TOPThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2013 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
namespace Think\Db\Driver;
use Think\Db;
defined('THINK_PATH') or exit();
/**
 * Mongo数据库驱动 必须配合MongoModel使用
 */
class Mongo extends Db{

    protected $_mongo           =   null; // MongoDb Object
    protected $_collection      =   null; // MongoCollection Object
    protected $_dbName          =   ''; // dbName
    protected $_collectionName  =   ''; // collectionName
    protected $_cursor          =   null; // MongoCursor Object
    protected $comparison       =   array('neq'=>'ne','ne'=>'ne','gt'=>'gt','egt'=>'gte','gte'=>'gte','lt'=>'lt','elt'=>'lte','lte'=>'lte','in'=>'in','not in'=>'nin','nin'=>'nin');

    /**
     * 架构函数 读取数据库配置信息
     * @access public
     * @param array $config 数据库配置数组
     */
    public function __construct($config=''){
        if ( !class_exists('mongoClient') ) {
            E(L('_NOT_SUPPERT_').':mongoClient');
        }
        if(!empty($config)) {
            $this->config   =   $config;
            if(empty($this->config['params'])) {
                $this->config['params'] =   array();
            }
        }
    }

    /**
     * 连接数据库方法
     * @access public
     */
    public function connect($config='',$linkNum=0) {
        if ( !isset($this->linkID[$linkNum]) ) {
            if(empty($config))  $config =   $this->config;
            $host = 'mongodb://'.($config['username']?"{$config['username']}":'').($config['password']?":{$config['password']}@":'').$config['hostname'].($config['hostport']?":{$config['hostport']}":'').'/'.($config['database']?"{$config['database']}":'');
            try{
                $this->linkID[$linkNum] = new \mongoClient( $host,$config['params']);
            }catch (\MongoConnectionException $e){
                E($e->getmessage());
            }
            // 标记连接成功
            $this->connected    =   true;
            // 注销数据库连接配置信息
            if(1 != C('DB_DEPLOY_TYPE')) unset($this->config);
        }
        return $this->linkID[$linkNum];
    }

    /**
     * 切换当前操作的Db和Collection
     * @access public
     * @param string $collection  collection
     * @param string $db  db
     * @param boolean $master 是否主服务器
     * @return void
     */
    public function switchCollection($collection,$db='',$master=true){
        // 当前没有连接 则首先进行数据库连接
        if ( !$this->_linkID ) $this->initConnect($master);
        try{
            if(!empty($db)) { // 传人Db则切换数据库
                // 当前MongoDb对象
                $this->_dbName  =  $db;
                $this->_mongo = $this->_linkID->selectDb($db);
            }
            // 当前MongoCollection对象
            if(C('DB_SQL_LOG')) {
                $this->queryStr   =  $this->_dbName.'.getCollection('.$collection.')';
            }
            if($this->_collectionName != $collection) {
                N('db_read',1);
                // 记录开始执行时间
                G('queryStartTime');
                $this->_collection =  $this->_mongo->selectCollection($collection);
                $this->debug();
                $this->_collectionName  = $collection; // 记录当前Collection名称
            }
        }catch (\MongoException $e){
            E($e->getMessage());
        }
    }

    /**
     * 释放查询结果
     * @access public
     */
    public function free() {
        $this->_cursor = null;
    }

    /**
     * 执行命令
     * @access public
     * @param array $command  指令
     * @return array
     */
    public function command($command=array()) {
        N('db_write',1);
        $this->queryStr = 'command:'.json_encode($command);
        // 记录开始执行时间
        G('queryStartTime');
        $result   = $this->_mongo->command($command);
        $this->debug();
        if(!$result['ok']) {
            E($result['errmsg']);
        }
        return $result;
    }

    /**
     * 执行语句
     * @access public
     * @param string $code  sql指令
     * @param array $args  参数
     * @return mixed
     */
    public function execute($code,$args=array()) {
        N('db_write',1);
        $this->queryStr = 'execute:'.$code;
        // 记录开始执行时间
        G('queryStartTime');
        $result   = $this->_mongo->execute($code,$args);
        $this->debug();
        if($result['ok']) {
            return $result['retval'];
        }else{
            E($result['errmsg']);
        }
    }

    /**
     * 关闭数据库
     * @access public
     */
    public function close() {
        if($this->_linkID) {
            $this->_linkID->close();
            $this->_linkID = null;
            $this->_mongo = null;
            $this->_collection =  null;
            $this->_cursor = null;
        }
    }

    /**
     * 数据库错误信息
     * @access public
     * @return string
     */
    public function error() {
        $this->error = $this->_mongo->lastError();
        trace($this->error,'','ERR');
        return $this->error;
    }

    /**
     * 插入记录
     * @access public
     * @param mixed $data 数据
     * @param array $options 参数表达式
     * @param boolean $replace 是否replace
     * @return false | integer
     */
    public function insert($data,$options=array(),$replace=false) {
        if(isset($options['table'])) {
            $this->switchCollection($options['table']);
        }
        $this->model  =   $options['model'];
        N('db_write',1);
        if(C('DB_SQL_LOG')) {
            $this->queryStr   =  $this->_dbName.'.'.$this->_collectionName.'.insert(';
            $this->queryStr   .= $data?json_encode($data):'{}';
            $this->queryStr   .= ')';
        }
        try{
            // 记录开始执行时间
            G('queryStartTime');
            $result =  $replace?   $this->_collection->save($data):  $this->_collection->insert($data);
            $this->debug();
            if($result) {
               $_id    = $data['_id'];
                if(is_object($_id)) {
                    $_id = $_id->__toString();
                }
               $this->lastInsID    = $_id;
            }
            return $result;
        } catch (\MongoCursorException $e) {
            E($e->getMessage());
        }
    }

    /**
     * 插入多条记录
     * @access public
     * @param array $dataList 数据
     * @param array $options 参数表达式
     * @return bool
     */
    public function insertAll($dataList,$options=array()) {
        if(isset($options['table'])) {
            $this->switchCollection($options['table']);
        }
        $this->model  =   $options['model'];
        N('db_write',1);
        try{
            // 记录开始执行时间
            G('queryStartTime');
           $result =  $this->_collection->batchInsert($dataList);
           $this->debug();
           return $result;
        } catch (\MongoCursorException $e) {
            E($e->getMessage());
        }
    }

    /**
     * 生成下一条记录ID 用于自增非MongoId主键
     * @access public
     * @param string $pk 主键名
     * @return integer
     */
    public function mongo_next_id($pk) {
        N('db_read',1);
        if(C('DB_SQL_LOG')) {
            $this->queryStr   =  $this->_dbName.'.'.$this->_collectionName.'.find({},{'.$pk.':1}).sort({'.$pk.':-1}).limit(1)';
        }
        try{
            // 记录开始执行时间
            G('queryStartTime');
            $result   =  $this->_collection->find(array(),array($pk=>1))->sort(array($pk=>-1))->limit(1);
            $this->debug();
        } catch (\MongoCursorException $e) {
            E($e->getMessage());
        }
        $data = $result->getNext();
        return isset($data[$pk])?$data[$pk]+1:1;
    }

    /**
     * 更新记录
     * @access public
     * @param mixed $data 数据
     * @param array $options 表达式
     * @return bool
     */
    public function update($data,$options) {
        if(isset($options['table'])) {
            $this->switchCollection($options['table']);
        }
        $this->model  =   $options['model'];
        N('db_write',1);
        $query   = $this->parseWhere($options['where']);
        $set  =  $this->parseSet($data);
        if(C('DB_SQL_LOG')) {
            $this->queryStr   =  $this->_dbName.'.'.$this->_collectionName.'.update(';
            $this->queryStr   .= $query?json_encode($query):'{}';
            $this->queryStr   .=  ','.json_encode($set).')';
        }
        try{
            // 记录开始执行时间
            G('queryStartTime');
            if(isset($options['limit']) && $options['limit'] == 1) {
                $multiple   =   array("multiple" => false);
            }else{
                $multiple   =   array("multiple" => true);
            }
            $result   = $this->_collection->update($query,$set,$multiple);
            $this->debug();
            return $result;
        } catch (\MongoCursorException $e) {
            E($e->getMessage());
        }
    }

    /**
     * 删除记录
     * @access public
     * @param array $options 表达式
     * @return false | integer
     */
    public function delete($options=array()) {
        if(isset($options['table'])) {
            $this->switchCollection($options['table']);
        }
        $query   = $this->parseWhere($options['where']);
        $this->model  =   $options['model'];
        N('db_write',1);
        if(C('DB_SQL_LOG')) {
            $this->queryStr   =  $this->_dbName.'.'.$this->_collectionName.'.remove('.json_encode($query).')';
        }
        try{
            // 记录开始执行时间
            G('queryStartTime');
            $result   = $this->_collection->remove($query);
            $this->debug();
            return $result;
        } catch (\MongoCursorException $e) {
            E($e->getMessage());
        }
    }

    /**
     * 清空记录
     * @access public
     * @param array $options 表达式
     * @return false | integer
     */
    public function clear($options=array()){
        if(isset($options['table'])) {
            $this->switchCollection($options['table']);
        }
        $this->model  =   $options['model'];
        N('db_write',1);
        if(C('DB_SQL_LOG')) {
            $this->queryStr   =  $this->_dbName.'.'.$this->_collectionName.'.remove({})';
        }
        try{
            // 记录开始执行时间
            G('queryStartTime');
            $result   =  $this->_collection->drop();
            $this->debug();
            return $result;
        } catch (\MongoCursorException $e) {
            E($e->getMessage());
        }
    }

    /**
     * 查找记录
     * @access public
     * @param array $options 表达式
     * @return iterator
     */
    public function select($options=array()) {
        if(isset($options['table'])) {
            $this->switchCollection($options['table'],'',false);
        }
        $cache  =  isset($options['cache'])?$options['cache']:false;
        if($cache) { // 查询缓存检测
            $key =  is_string($cache['key'])?$cache['key']:md5(serialize($options));
            $value   =  S($key,'','',$cache['type']);
            if(false !== $value) {
                return $value;
            }
        }
        $this->model  =   $options['model'];
        N('db_query',1);
        $query  =  $this->parseWhere($options['where']);
        $field =  $this->parseField($options['field']);
        try{
            if(C('DB_SQL_LOG')) {
                $this->queryStr   =  $this->_dbName.'.'.$this->_collectionName.'.find(';
                $this->queryStr  .=  $query? json_encode($query):'{}';
                $this->queryStr  .=  $field? ','.json_encode($field):'';
                $this->queryStr  .=  ')';
            }
            // 记录开始执行时间
            G('queryStartTime');
            $_cursor   = $this->_collection->find($query,$field);
            if($options['order']) {
                $order   =  $this->parseOrder($options['order']);
                if(C('DB_SQL_LOG')) {
                    $this->queryStr .= '.sort('.json_encode($order).')';
                }
                $_cursor =  $_cursor->sort($order);
            }
            if(isset($options['page'])) { // 根据页数计算limit
                if(strpos($options['page'],',')) {
                    list($page,$length) =  explode(',',$options['page']);
                }else{
                    $page    = $options['page'];
                }
                $page    = $page?$page:1;
                $length = isset($length)?$length:(is_numeric($options['limit'])?$options['limit']:20);
                $offset  =  $length*((int)$page-1);
                $options['limit'] =  $offset.','.$length;
            }
            if(isset($options['limit'])) {
                list($offset,$length) =  $this->parseLimit($options['limit']);
                if(!empty($offset)) {
                    if(C('DB_SQL_LOG')) {
                        $this->queryStr .= '.skip('.intval($offset).')';
                    }
                    $_cursor =  $_cursor->skip(intval($offset));
                }
                if(C('DB_SQL_LOG')) {
                    $this->queryStr .= '.limit('.intval($length).')';
                }
                $_cursor =  $_cursor->limit(intval($length));
            }
            $this->debug();
            $this->_cursor =  $_cursor;
            $resultSet  =  iterator_to_array($_cursor);
            if($cache && $resultSet ) { // 查询缓存写入
                S($key,$resultSet,$cache['expire'],$cache['type']);
            }
            return $resultSet;
        } catch (\MongoCursorException $e) {
            E($e->getMessage());
        }
    }

    /**
     * 查找某个记录
     * @access public
     * @param array $options 表达式
     * @return array
     */
    public function find($options=array()){
        if(isset($options['table'])) {
            $this->switchCollection($options['table'],'',false);
        }
        $cache  =  isset($options['cache'])?$options['cache']:false;
        if($cache) { // 查询缓存检测
            $key =  is_string($cache['key'])?$cache['key']:md5(serialize($options));
            $value   =  S($key,'','',$cache['type']);
            if(false !== $value) {
                return $value;
            }
        }
        $this->model  =   $options['model'];
        N('db_query',1);
        $query  =  $this->parseWhere($options['where']);
        $fields    = $this->parseField($options['field']);
        if(C('DB_SQL_LOG')) {
            $this->queryStr = $this->_dbName.'.'.$this->_collectionName.'.findOne(';
            $this->queryStr .= $query?json_encode($query):'{}';
            $this->queryStr .= $fields?','.json_encode($fields):'';
            $this->queryStr .= ')';
        }
        try{
            // 记录开始执行时间
            G('queryStartTime');
            $result   = $this->_collection->findOne($query,$fields);
            $this->debug();
            if($cache && $result ) { // 查询缓存写入
                S($key,$result,$cache['expire'],$cache['type']);
            }
            return $result;
        } catch (\MongoCursorException $e) {
            E($e->getMessage());
        }
    }

    /**
     * 统计记录数
     * @access public
     * @param array $options 表达式
     * @return iterator
     */
    public function count($options=array()){
        if(isset($options['table'])) {
            $this->switchCollection($options['table'],'',false);
        }
        $this->model  =   $options['model'];
        N('db_query',1);
        $query  =  $this->parseWhere($options['where']);
        if(C('DB_SQL_LOG')) {
            $this->queryStr   =  $this->_dbName.'.'.$this->_collectionName;
            $this->queryStr   .= $query?'.find('.json_encode($query).')':'';
            $this->queryStr   .= '.count()';
        }
        try{
            // 记录开始执行时间
            G('queryStartTime');
            $count   = $this->_collection->count($query);
            $this->debug();
            return $count;
        } catch (\MongoCursorException $e) {
            E($e->getMessage());
        }
    }

    public function group($keys,$initial,$reduce,$options=array()){
        $this->_collection->group($keys,$initial,$reduce,$options);
    }

    /**
     * 取得数据表的字段信息
     * @access public
     * @return array
     */
    public function getFields($collection=''){
        if(!empty($collection) && $collection != $this->_collectionName) {
            $this->switchCollection($collection,'',false);
        }
        N('db_query',1);
        if(C('DB_SQL_LOG')) {
            $this->queryStr   =  $this->_dbName.'.'.$this->_collectionName.'.findOne()';
        }
        try{
            // 记录开始执行时间
            G('queryStartTime');
            $result   =  $this->_collection->findOne();
            $this->debug();
        } catch (\MongoCursorException $e) {
            E($e->getMessage());
        }
        if($result) { // 存在数据则分析字段
            $info =  array();
            foreach ($result as $key=>$val){
                $info[$key] =  array(
                    'name'=>$key,
                    'type'=>getType($val),
                    );
            }
            return $info;
        }
        // 暂时没有数据 返回false
        return false;
    }

    /**
     * 取得当前数据库的collection信息
     * @access public
     */
    public function getTables(){
        if(C('DB_SQL_LOG')) {
            $this->queryStr   =  $this->_dbName.'.getCollenctionNames()';
        }
        N('db_query',1);
        // 记录开始执行时间
        G('queryStartTime');
        $list   = $this->_mongo->listCollections();
        $this->debug();
        $info =  array();
        foreach ($list as $collection){
            $info[]   =  $collection->getName();
        }
        return $info;
    }

    /**
     * set分析
     * @access protected
     * @param array $data
     * @return string
     */
    protected function parseSet($data) {
        $result   =  array();
        foreach ($data as $key=>$val){
            if(is_array($val)) {
                switch($val[0]) {
                    case 'inc':
                        $result['$inc'][$key]  =  (int)$val[1];
                        break;
                    case 'set':
                    case 'unset':
                    case 'push':
                    case 'pushall':
                    case 'addtoset':
                    case 'pop':
                    case 'pull':
                    case 'pullall':
                        $result['$'.$val[0]][$key] = $val[1];
                        break;
                    default:
                        $result['$set'][$key] =  $val;
                }
            }else{
                $result['$set'][$key]    = $val;
            }
        }
        return $result;
    }

    /**
     * order分析
     * @access protected
     * @param mixed $order
     * @return array
     */
    protected function parseOrder($order) {
        if(is_string($order)) {
            $array   =  explode(',',$order);
            $order   =  array();
            foreach ($array as $key=>$val){
                $arr  =  explode(' ',trim($val));
                if(isset($arr[1])) {
                    $arr[1]  =  $arr[1]=='asc'?1:-1;
                }else{
                    $arr[1]  =  1;
                }
                $order[$arr[0]]    = $arr[1];
            }
        }
        return $order;
    }

    /**
     * limit分析
     * @access protected
     * @param mixed $limit
     * @return array
     */
    protected function parseLimit($limit) {
        if(strpos($limit,',')) {
            $array  =  explode(',',$limit);
        }else{
            $array   =  array(0,$limit);
        }
        return $array;
    }

    /**
     * field分析
     * @access protected
     * @param mixed $fields
     * @return array
     */
    public function parseField($fields){
        if(empty($fields)) {
            $fields    = array();
        }
        if(is_string($fields)) {
            $fields    = explode(',',$fields);
        }
        return $fields;
    }

    /**
     * where分析
     * @access protected
     * @param mixed $where
     * @return array
     */
    public function parseWhere($where){
        $query   = array();
        foreach ($where as $key=>$val){
            if('_id' != $key && 0===strpos($key,'_')) {
                // 解析特殊条件表达式
                $query   = $this->parseThinkWhere($key,$val);
            }else{
                // 查询字段的安全过滤
                if(!preg_match('/^[A-Z_\|\&\-.a-z0-9]+$/',trim($key))){
                    E(L('_ERROR_QUERY_').':'.$key);
                }
                $key = trim($key);
                if(strpos($key,'|')) {
                    $array   =  explode('|',$key);
                    $str   = array();
                    foreach ($array as $k){
                        $str[]   = $this->parseWhereItem($k,$val);
                    }
                    $query['$or'] =    $str;
                }elseif(strpos($key,'&')){
                    $array   =  explode('&',$key);
                    $str   = array();
                    foreach ($array as $k){
                        $str[]   = $this->parseWhereItem($k,$val);
                    }
                    $query   = array_merge($query,$str);
                }else{
                    $str   = $this->parseWhereItem($key,$val);
                    $query   = array_merge($query,$str);
                }
            }
        }
        return $query;
    }

    /**
     * 特殊条件分析
     * @access protected
     * @param string $key
     * @param mixed $val
     * @return string
     */
    protected function parseThinkWhere($key,$val) {
        $query   = array();
        switch($key) {
            case '_query': // 字符串模式查询条件
                parse_str($val,$query);
                if(isset($query['_logic']) && strtolower($query['_logic']) == 'or' ) {
                    unset($query['_logic']);
                    $query['$or']   =  $query;
                }
                break;
            case '_string':// MongoCode查询
                $query['$where']  = new \MongoCode($val);
                break;
        }
        return $query;
    }

    /**
     * where子单元分析
     * @access protected
     * @param string $key
     * @param mixed $val
     * @return array
     */
    protected function parseWhereItem($key,$val) {
        $query   = array();
        if(is_array($val)) {
            if(is_string($val[0])) {
                $con  =  strtolower($val[0]);
                if(in_array($con,array('neq','ne','gt','egt','gte','lt','lte','elt'))) { // 比较运算
                    $k = '$'.$this->comparison[$con];
                    $query[$key]  =  array($k=>$val[1]);
                }elseif('like'== $con){ // 模糊查询 采用正则方式
                    $query[$key]  =  new \MongoRegex("/".$val[1]."/");  
                }elseif('mod'==$con){ // mod 查询
                    $query[$key]   =  array('$mod'=>$val[1]);
                }elseif('regex'==$con){ // 正则查询
                    $query[$key]  =  new \MongoRegex($val[1]);
                }elseif(in_array($con,array('in','nin','not in'))){ // IN NIN 运算
                    $data = is_string($val[1])? explode(',',$val[1]):$val[1];
                    $k = '$'.$this->comparison[$con];
                    $query[$key]  =  array($k=>$data);
                }elseif('all'==$con){ // 满足所有指定条件
                    $data = is_string($val[1])? explode(',',$val[1]):$val[1];
                    $query[$key]  =  array('$all'=>$data);
                }elseif('between'==$con){ // BETWEEN运算
                    $data = is_string($val[1])? explode(',',$val[1]):$val[1];
                    $query[$key]  =  array('$gte'=>$data[0],'$lte'=>$data[1]);
                }elseif('not between'==$con){
                    $data = is_string($val[1])? explode(',',$val[1]):$val[1];
                    $query[$key]  =  array('$lt'=>$data[0],'$gt'=>$data[1]);
                }elseif('exp'==$con){ // 表达式查询
                    $query['$where']  = new \MongoCode($val[1]);
                }elseif('exists'==$con){ // 字段是否存在
                    $query[$key]  =array('$exists'=>(bool)$val[1]);
                }elseif('size'==$con){ // 限制属性大小
                    $query[$key]  =array('$size'=>intval($val[1]));
                }elseif('type'==$con){ // 限制字段类型 1 浮点型 2 字符型 3 对象或者MongoDBRef 5 MongoBinData 7 MongoId 8 布尔型 9 MongoDate 10 NULL 15 MongoCode 16 32位整型 17 MongoTimestamp 18 MongoInt64 如果是数组的话判断元素的类型
                    $query[$key]  =array('$type'=>intval($val[1]));
                }else{
                    $query[$key]  =  $val;
                }
                return $query;
            }
        }
        $query[$key]  =  $val;
        return $query;
    }
}