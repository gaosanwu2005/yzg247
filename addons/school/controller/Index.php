<?php

namespace addons\school\controller;

use think\addons\Controller;
use think\Db;
use tree\Tree2;

class Index extends Controller
{
    protected $noNeedLogin = ['reg', 'successinfo'];
    protected $noNeedRight = '*';
    protected $layout = '';

    public function _empty()
    {
        return $this->view->fetch();
    }

    public function index()
    {
        $categorys = Db::name('category')->where('type', 'record')->field('pid as tjid,id,name,image,description,status')->select();
        $tree = new Tree2();
        $tree->init($categorys);
        $treeStr = $tree->getTreeArray5(0);
        $this->assign('record', $treeStr);

        return $this->view->fetch();
    }

    public function index2()
    {
        $record = Db::name('category')->where('type', 'record')->field('id,name')->select();
        $classtype = Db::name('category')->where('type', 'classtype')->field('id,name')->select();

        $infos = Db::name('school_info')->where('is_on', '1')->select();
        $this->assign('infos', $infos);
        $this->assign('record', json_encode($record));
        $this->assign('classtype', json_encode($classtype));

        return $this->view->fetch();
    }

    public function xuelitisheng()
    {
        $user = $this->auth->getUserinfo();
        $info = Db::name('school_resume')->find($user['id']);
        $this->assign('info', $info);

        return $this->view->fetch();
    }

    public function xuelitisheng2()
    {
        $record = Db::name('category')->where('type', 'record')->field('id,name')->select();  //提升层次
        $classtype = Db::name('category')->where('type', 'classtype')->field('id,name')->select();   //上课类型
        $professional = Db::name('category')->where('type', 'professional')->field('id,name')->select(); //专业
        $school = Db::name('category')->where('type', 'school')->field('id,name')->select(); //学校
        $this->assign('school', $school);
        $this->assign('professional', $professional);
        $this->assign('record', $record);
        $this->assign('classtype', $classtype);
        $user = $this->auth->getUserinfo();
        $info = Db::name('school_resume')->find($user['id']);
        $this->assign('info', $info);

        return $this->view->fetch();
    }
}
