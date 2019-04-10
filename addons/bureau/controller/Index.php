<?php

namespace addons\bureau\controller;

use think\addons\Controller;
use think\Db;

class Index extends Controller
{
    protected $noNeedLogin = ['reg', 'successinfo'];
    protected $noNeedRight = '*';
    protected $layout = '';

    public function _empty()
    {
        return $this->view->fetch();
    }

    /**
     * 安监局报名流程.
     */
    public function anjianju()
    {
        return $this->view->fetch();
    }

    /**
     * 质监局报名流程.
     */
    public function zhijianju()
    {
        return $this->view->fetch();
    }

    /**
     * 人保部报名流程.
     */
    public function renbaobu()
    {
        return $this->view->fetch();
    }

    /**
     * 司局报名须知.
     */
    public function report()
    {
        $type = input('');
        $this->assign('bureau_type', $type['bureau_type']);
        return $this->view->fetch();
    }

    /**
     * 司局报名协议.
     */
    public function report2()
    {
        $type = input('');
        $this->assign('bureau_type', $type['bureau_type']);
        return $this->view->fetch();
    }

    /**
     * 司局报名个人信息.
     */
    public function online()
    {
        $type = input('');
        $this->assign('bureau_type', $type['bureau_type']);
        $user = $this->auth->getUserinfo();
        //判断用户在对应司局是否存在
        $data['uid'] = $user['id'];
        $data['bureau_type'] = $type['bureau_type'];
        $info = Db::name('bureau')->where($data)->find();
        $this->assign('info', $info);

        return $this->view->fetch();
    }

    /**
     * 司局报名考点科目.
     */
    public function online2()
    {
        $type = input('');
        $this->assign('bureau_type', $type['bureau_type']);

        $user = $this->auth->getUserinfo();
        //判断用户在对应司局是否存在
        $data['uid'] = $user['id'];
        $data['bureau_type'] = $type['bureau_type'];
        $info = Db::name('bureau')->where($data)->find();
        $this->assign('info', $info);

        $province = Db::name('area')->where('pid', 0)->field('id,name')->select();  //省级
        if ($info['city']) {
            $area = Db::name('area')->where('name', $info['city'])->field('pid, id, name')->find();  //市区
            $city = Db::name('area')->where('pid', $area['pid'])->field('name')->select();
        } else {
            $city = array();
        }

        if ($info['category2']) {
            $cate = Db::name('category')->where('name', $info['category2'])->field('pid, id, name')->find();  //市区
            $category2 = Db::name('category')->where('pid', $cate['pid'])->field('name')->select();
        } else {
            $category2 = array();
        }

        //获取司局一级分类条件
        if ($type['bureau_type'] == 1) {
            $subject = Db::name('category')->where('type', 'subject')->where('pid',0)->where('id', '>=', 144)->where('id', '<=', 151)->field('id,pid,name')->select();
        } elseif ($type['bureau_type'] == 2) {
            $subject = Db::name('category')->where('type', 'subject')->where('pid',0)->where('id', '>=', 197)->where('id', '<=', 207)->field('id,pid,name')->select();
        } else {
            $subject = Db::name('category')->where('type', 'subject')->where('pid',0)->where('id', '>=', 253)->where('id', '<=', 261)->field('id,pid,name')->select();
        }

        if ($type['bureau_type'] == 3) {
            $grade = Db::name('category')->where('type', 'grade')->field('id,pid,name')->select();
        } else {
            $grade = array();
        }
        $this->assign('grade', $grade);
        $this->assign('subject', $subject);
        $this->assign('province', $province);
        $this->assign('city', $city);
        $this->assign('category2', $category2);

        return $this->view->fetch();
    }

    /**
     * 多级分类.
     */
    public function soncategory()
    {
        $name = input('get.');
        if ($name['type'] == 1) {
            $subject = Db::name('area')->where('name',$name['province'])->field('id')->find();
            $category = Db::name('area')->where('pid',$subject['id'])->field('name')->select();
        } elseif ($name['type'] == 2) {
            $subject = Db::name('category')->where('name',$name['subject'])->field('id')->find();
            $category = Db::name('category')->where('pid',$subject['id'])->field('name')->select();
        } else {
            if ($name['subject'] == '起重机装卸机械操作员' || $name['subject'] == '铸造工' || $name['subject'] == '锻造工' || $name['subject'] == '金属热处理工' || $name['subject'] == '锅炉运行值班员' || $name['subject'] == '锅炉操作工') {
                $category = Db::name('category')->where('name', '高技')->field('id,pid,name')->select();
            } else {
                $category = Db::name('category')->where('type', 'grade')->field('id,pid,name')->select();
            }
        }
        $data['category'] = $category;
        return json($data);
    }
}
