<?php

namespace app\admin\controller;

use app\common\controller\Backend;

use app\common\library\Sms as Smslib;

/**
 * 司局报名信息
 *
 * @icon fa fa-circle-o
 */
class Bureau extends Backend
{
    
    /**
     * Bureau模型对象
     * @var \app\admin\model\Bureau
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('Bureau');
        $this->view->assign("idtypeList", $this->model->getIdtypeList());
        $this->view->assign("statusList", $this->model->getStatusList());
        $this->view->assign("bureauTypeList", $this->model->getBureauTypeList());
    }

    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */
    public function apply()
    {
        $id = input('ids');
        if (!$id > 0) {
            $this->error("参数有误");
        }

        if ($this->request->isPost()) {
            $op = input('post.op');

            if ($op == 1) {
                //审核通过
                $rr = $this->model->where('id', $id)->setField('status', '2');
                $info = $this->model->where('id', $id)->find();
                //给用户发送手机通知
                if ($info['mobile']) {
                    $ret = Smslib::send($info['mobile'], '3_'.$info['bureau_type']);
                } else {
                    $this->error("手机短信通知失败，用户手机号为空。");
                }
            } else {
                //审核未通过
                $rr = $this->model->where('id', $id)->setField('status', '3');
            }

            if ($rr == 1 && $ret) {
                $this->success("操作成功，已手机短信通知用户");
            } else {
                $this->error("操作失败");
            }
        }
        $this->view->assign('id', $id);
        return $this->view->fetch();
    }


}
