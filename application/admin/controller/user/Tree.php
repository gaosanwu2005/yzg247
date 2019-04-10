<?php

namespace app\admin\controller\user;

use app\common\controller\Backend;
use think\Db;
use tree\Tree2;

/**
 * 网络管理
 *
 * @icon fa fa-user
 */
class Tree extends Backend
{

    protected $relationSearch = true;

    /**
     * User模型对象
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\User();
    }

    /**
     * 查看
     */
    public function index()
    {
        return $this->view->fetch();
    }

    public function mytree()
    { 
        $myid = input('userinfo', 0);
        $map['id|username|mobile'] = $myid;
        $info = $this->model->where($map)->order('id desc')->find();


        $categories = Db::name('user')->select();

        $tree = new Tree2();

        $newCategories = [];

        foreach ($categories as $item) {
            $jihuo = $item['jointime'] > 0 ? '已激活' : '未激活';
            $dongjie = $item['status'] != '1' ? '已冻结' : '正常';
            $item['name'] = '  等级:' . $item['level'] . '  用户名：' . $item['username'] . '  用户id：' . $item['id'] . '  团队人数:' . $item['tdnum'] . '  直推人数:' . $item['ztnum'] . '-----状态：' . $dongjie . $jihuo. '  本金钱包:' . $item['wall3'];
            array_push($newCategories, $item);
        }

        if ($info) {
            $myid = $info['id'];
            $tree->init($newCategories);
            $treeStr = $tree->getTreeArray2($myid);
            $name = '第1层--' . '  等级:' . $info['level'] . '  用户名：' . $info['username'] . '  用户id：' . $info['id'] . '  团队人数:' . $info['tdnum'] . '  直推人数:' . $info['ztnum']. '  本金钱包:' . $info['wall3'];
            $returnArray[] = array('name' => $name, 'children' => $treeStr);
            return json($returnArray);
        } else {
            $myid = 0;
            $tree->init($newCategories);
            $treeStr = $tree->getTreeArray2($myid);
            $returnArray[] = array('name' => '全体', 'children' => $treeStr);

           return json($returnArray);
        }


    }




}
