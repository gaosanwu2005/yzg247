<?php

namespace app\admin\controller\trade;

use app\common\controller\Backend;
use think\Db;

/**
 * 交易管理
 *
 * @icon fa fa-circle-o
 */
class Ppmx extends Backend
{
    
    /**
     * Ppmx模型对象
     * @var \app\admin\model\Ppmx
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('Ppmx');
        $this->view->assign("typeList", $this->model->getTypeList());
        $this->view->assign("statusList", $this->model->getStatusList());
    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    /**
     * 虚拟币交易管理 取消 确认
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
                ppcancle($ppid);
                $rr=  $this->model->where('ppid', $ppid)->setField(['reply'=>$desc]);
            } else {        //确认
                $rr= $this->model->where('ppid', $ppid)->setField(['confirmtime'=>time(),'reply'=>$desc,'status'=>'2']);
                $buyuid = $rspp['userid'];
                $saleuid = $rspp['userid1'];
                //增加买家WALL2
                caiwu($buyuid, $rspp['number'], 2, 'wall2', '投资买入');
                //增加买家交易额度
                caiwu($buyuid, $rspp['number']*1.7, 2, 'wall7', '投资买入增加授信额度');
                //减少卖家冻结
                $fee = round($rspp['number'] * $myset['salefee'] * 0.01, 4);
                $total = $rspp['number']+$fee;
                db('user')->where(array('id' => $saleuid))->setDec('freeze2', $total);
                //减少卖家授信额度冻结
                db('user')->where(array('id' => $saleuid))->setDec('freeze7', $rspp['number']);
                //减少卖家令牌冻结
                $v1=ceil($rspp['number']/50);
                db('user')->where(array('id' => $saleuid))->setDec('freezev1',$v1);
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
