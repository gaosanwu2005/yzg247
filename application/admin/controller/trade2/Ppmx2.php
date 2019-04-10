<?php

namespace app\admin\controller\trade2;

use app\common\controller\Backend;
use think\Db;

/**
 * 交易管理
 *
 * @icon fa fa-circle-o
 */
class Ppmx2 extends Backend
{
    
    /**
     * Ppmx2模型对象
     * @var \app\admin\model\Ppmx2
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('Ppmx2');
        $this->view->assign("typeList", $this->model->getTypeList());
        $this->view->assign("statusList", $this->model->getStatusList());
    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */
    /**
     * 互助交易管理 取消 确认
     */
    public function pphand()
    {
        $ppid = input('ids');
        $myset = config('site');
        if (!$ppid > 0)
            $this->error("参数有误");
        if ($this->request->isPost()) {
            $op = input('post.op');
            $desc = input('post.desc');
            if (!$desc){
                $this->error("请填写操作说明");
            }
            $rspp = $this->model->get($ppid);
            if (!$rspp || $rspp['confirmtime'] > 0) {
                $this->error('获取匹配失败，或已经确认收款');
            }
            if ($op == 1) {   //取消
                ppcancle2($ppid);
                $rr=  $this->model->where('ppid', $ppid)->setField(['reply'=>$desc]);
            } else {        //确认
                $rr= $this->model->where('ppid', $ppid)->setField(['confirmtime'=>time(),'reply'=>$desc,'status'=>'2']);
                $buyuid = $rspp['userid'];
                Db::name('user')->where(array('id' => $buyuid))->setInc('tzprice', $rspp['total']);
                Db::name('user')->where(array('id' => $buyuid))->setInc('futou', 1);     //互助次数

                caiwu($buyuid, $rspp['number'], 2, 'wall3', '投资买入');
                //5小时内打款 诚信奖励
                if (($rspp['paytime'] - $rspp['addtime']) <= $myset['cxtime'] * 3600) {
                    $profit = $rspp['number'] * $myset['cxrate'] * 0.01;
                    caiwu($buyuid, $profit, 9, 'wall4', '诚信奖励金');
                }

                $kk = $rspp['number'] * 10 * 0.01;
                caiwu($buyuid, $kk, 9, 'wall4', '静态余额');     //静态
            }
            if ($rr == 1) {
                $this->success("操作成功");
            } else {
                $this->error("操作失败");
            }
        }
        $this->view->assign('ppid', $ppid);
        return $this->view->fetch();
    }

}
