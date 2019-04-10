<?php

namespace app\admin\controller\trade2;

use app\common\controller\Backend;
use think\Db;

/**
 * 卖出管理
 *
 * @icon fa fa-circle-o
 */
class Xymx2 extends Backend
{
     
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('Xymx2');
        $this->view->assign("ptypeList", $this->model->getPtypeList());
        $this->view->assign("statusList", $this->model->getStatusList());
    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */
    /**
     * 手动匹配
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function sdpp()
    {
        $xyid = input('ids');

        $myset = config('site');
        $tgmx = Db::name('tgmx2');
        $xymx = Db::name('xymx2');

        $ptype = input('wall');
        if ($ptype != "") {
            $map['ptype'] = $ptype;
        }
        $rstg = $xymx->where(array('xyid' => $xyid, 'status' => 0))->find();
        if (!$rstg) $this->error('已完成或者不存在');

        $map['userid'] = array('neq', $rstg['userid']);
        $map['status'] = 0;
        $list = $tgmx->where($map)->order('addtime asc')->select();
        $tgleft = $rstg['number'] - $rstg['sale_number'];
        if ($list) {
            foreach ($list as $key => $item) {
                $qgleft = $item['number'] - $item['buy_number'];
                $list[$key]['pp'] = min($tgleft, $qgleft);
                $tgleft -= $list[$key]['pp'];
            }
        }
        $this->assign('rstg', $rstg);
        $this->assign('list', $list);
        $this->assign('ptype', $myset['walltype']);
        return $this->view->fetch();

    }

    //批量匹配
    public function sdpp2()
    {
        $Ppmx2 = new \app\common\model\Ppmx2();
        $sale_ids = input('sale_id/a');
        $sale_prices = input('sale_price/a');
        $buyid = input('buy_id');
        if (empty($sale_ids)) {
            $this->error('请选择要匹配的记录');
        }
        for ($i = 0; $i < count($sale_ids); $i++) {
            try {
               $Ppmx2->findSale2($sale_ids[$i], $buyid,$sale_prices[$i]);
            } catch (Exception $e) {
                $data['status'] = 0;
                $data['info'] = '匹配失败，请检查匹配金额！'.$Ppmx2->getError();
                return json($data);
            }
        }

        $data['status'] = 1;
        $data['info'] = '匹配成功！';
        $data['url'] = '';
        return json($data);

    }

    //自定义匹配
    public function sdpp3()
    {
        $Ppmx2 = new \app\common\model\Ppmx2();
        $buyid = input('tgid');
        $saleid = input('xyid');
        $number = input('number');
        $rt = $Ppmx2->findSale2($buyid, $saleid, $number);
        if ($rt) {
            $this->success('匹配成功！');
        } else {
            $this->error('匹配失败，请检查匹配金额！');
        }
    }

}
