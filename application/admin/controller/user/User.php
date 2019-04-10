<?php

namespace app\admin\controller\user;

use app\common\controller\Backend;
use think\Db;

/**
 * 会员管理
 *
 * @icon fa fa-user
 */
class User extends Backend
{
    
    /**
     * User模型对象
     * @var \app\admin\model\User
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('User');
        $this->view->assign("levelList", $this->model->getLevelList());
        $this->view->assign("genderList", $this->model->getGenderList());
        $this->view->assign("loginfailureList", $this->model->getLoginfailureList());
        $this->view->assign("statusList", $this->model->getStatusList());
    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */
    

    /**
     * 查看
     */
    public function index()
    {
        //当前是否为关联查询
        $this->relationSearch = false;
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax())
        {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField'))
            {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                    
                    ->where($where)
                    ->order($sort, $order)
                    ->count();

            $list = $this->model
                    
                    ->where($where)
                    ->order($sort, $order)
                    ->limit($offset, $limit)
                    ->select();

//            foreach ($list as $row) {
//                $row->visible(['id','username','nickname','mobile','avatar','wall1','wall2','wall3','wall4','wall5','wall6','wall7','wall8','level','loginip','jointime','createtime','tjuser','ztnum','tdnum','slrate','tdsl','tzprice','extsl','status','txwall2','txwall3','txwall6','futou']);
//
//            }
            $list = collection($list)->toArray();
          
            $result = array("total" => $total, "rows" => $list, "where" => $this->buildparams());

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 临时会员查看
     */
    public function index2()
    {
        //当前是否为关联查询
        $this->relationSearch = false;
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax())
        {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField'))
            {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

//            if(is_array($where)) {
//                $where=array_merge($where,['jointime'=>0]);
//            }else{
//                $where=['jointime'=>0];
//            }
            $total = $this->model

                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model

                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

//            foreach ($list as $row) {
//                $row->visible(['id','username','nickname','mobile','avatar','wall1','wall2','wall3','wall4','wall5','wall6','wall7','wall8','level','loginip','jointime','createtime','tjuser','ztnum','tdnum','slrate','tdsl','tzprice','extsl','status','txwall2','txwall3','txwall6','futou']);
//
//            }
            $list = collection($list)->toArray();

            $result = array("total" => $total, "rows" => $list, "where" => $order);

            return json($result);
        }
        return $this->view->fetch('index');
    }

    /**
     * 代理申请查看
     */
    public function index3()
    {
        //当前是否为关联查询
        $this->relationSearch = false;
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax())
        {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField'))
            {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model

                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model

                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

//            foreach ($list as $row) {
//                $row->visible(['id','username','nickname','mobile','avatar','wall1','wall2','wall3','wall4','wall5','wall6','wall7','wall8','level','loginip','jointime','createtime','tjuser','ztnum','tdnum','slrate','tdsl','tzprice','extsl','status','txwall2','txwall3','txwall6','futou']);
//
//            }
            $list = collection($list)->toArray();

            $result = array("total" => $total, "rows" => $list, "where" => $this->buildparams());

            return json($result);
        }
        return $this->view->fetch('index');
    }

    /**
     * 店铺申请查看
     */
    public function index4()
    {
        //当前是否为关联查询
        $this->relationSearch = false;
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax())
        {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField'))
            {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model

                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model

                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

//            foreach ($list as $row) {
//                $row->visible(['id','username','nickname','mobile','avatar','wall1','wall2','wall3','wall4','wall5','wall6','wall7','wall8','level','loginip','jointime','createtime','tjuser','ztnum','tdnum','slrate','tdsl','tzprice','extsl','status','txwall2','txwall3','txwall6','futou']);
//
//            }
            $list = collection($list)->toArray();

            $result = array("total" => $total, "rows" => $list, "where" => $this->buildparams());

            return json($result);
        }
        return $this->view->fetch('index');
    }

    /**
     * 实体店铺申请查看
     */
    public function index5()
    {
        //当前是否为关联查询
        $this->relationSearch = false;
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax())
        {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField'))
            {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model

                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model

                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

//            foreach ($list as $row) {
//                $row->visible(['id','username','nickname','mobile','avatar','wall1','wall2','wall3','wall4','wall5','wall6','wall7','wall8','level','loginip','jointime','createtime','tjuser','ztnum','tdnum','slrate','tdsl','tzprice','extsl','status','txwall2','txwall3','txwall6','futou']);
//
//            }
            $list = collection($list)->toArray();

            $result = array("total" => $total, "rows" => $list, "where" => $this->buildparams());

            return json($result);
        }
        return $this->view->fetch('index');
    }

    /**
     * 编辑
     */
    public function edit($ids = NULL)
    {
        $row = $this->model->get($ids);
        if (!$row) $this->error(__('No Results were found'));
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                try {
                    $params['password'] = authcode($params['password']);
                    $params['password2'] = authcode($params['password2']);
                    $result = $row->allowField(true)->save($params);
                    if ($result !== false) {
                        $this->success();
                    } else {
                        $this->error($row->getError());
                    }
                } catch (\think\exception\PDOException $e) {
                    $this->error($e->getMessage());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->view->assign("row", $row);
        $this->view->assign('groupList', build_select('row[group_id]', \app\admin\model\UserGroup::column('id,name'), $row['group_id'], ['class' => 'form-control selectpicker']));

        return $this->view->fetch();
    }

    /**
     * 添加
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                    $params[$this->dataLimitField] = $this->auth->id;
                }
                try {

                    $validate = new \app\admin\validate\User;
                    $result = $validate->scene('add')->check($params);
                    if (!$result) {
                        $this->error(__($validate->getError()));
                    }
                    $params['password'] = authcode($params['password']);
                    $params['password2'] = authcode($params['password2']);
                    $params['joinip'] = request()->ip();
                    $params['createtime'] = time();
                    if ($params['tjuser'] != '') {
                        $usermodel = new \app\common\model\User;
                        $tjuser = $usermodel->getByUsername($params['tjuser']);
                        $params['tjid'] = $tjuser['id'];
                        $params['ztlevel'] = $tjuser['ztlevel'] + 1;
                        $params['tpath'] = $tjuser['tpath'] . "," . $tjuser['id'];
                        Db::name('user')->where('id', $tjuser['id'])->setInc('lmccj',1); //增加抽奖次数
                    } else {
                        $params['tpath'] = 0;
                    }
                    $params['tgno'] = md5(time().$params['username']);
                    $params['walladress'] = md5(time() . $params['mobile']);
                    $newid = Db::name('user')->strict(true)->insertGetId($params);
                    if ($newid) {
                        update_user_tui($newid);
                        $this->success('添加成功');
                    } else {
                        $this->error($this->auth->getError());
                    }

                    if ($result !== false) {
                        $this->success();
                    } else {
                        $this->error($this->model->getError());
                    }
                } catch (\think\exception\PDOException $e) {
                    $this->error($e->getMessage());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        return $this->view->fetch();
    }

    /**
     * 激活
     */
    public function jhuser()
    {
        if ($this->request->isAjax()) {
            $uid = $this->request->param('ids');

            $rshy = $this->model->where(array('id' => $uid))->find();
            if ($rshy ['jointime'] > 0) {
                $this->error("会员已经激活！");
            }
            // 会员激活
            $upary ['jointime'] = time();
            if ($this->model->where(array('id' => $uid))->setField($upary) === false) {
                $this->error('激活失败！');
            }
            update_user_tui($uid,1);
            $this->success("激活成功");
        }
    }

    /**
     * 账户资金调节
     */
    public function account_edit()
    {
        $user_id = input('ids');
        if (!$user_id > 0)
            $this->error("参数有误");
        if ($this->request->isPost()) {
            $wallet = input('post.wallet');
            $op = input('post.op');
            $price = input('post.price');
            $desc = input('post.desc');
            if (!$price || $price <= 0) {
                $this->error("请填写正确的金额");
            }
            if (!$desc)
                $this->error("请填写操作说明");
            if ($op == 2) {
                $price = -$price;
            }
            $admin_info = session('admin.username');
            if (caiwu($user_id, $price, 1, $wallet, "管理员:{$desc}")) {
                $this->success("操作成功");
            } else {
                $this->error("操作失败");
            }
            exit;
        }

        $this->view->assign('user_id', $user_id);
        $this->view->assign('walltype', \config('site.walltype'));
        return $this->view->fetch();
    }

    /**
     * 送矿机
     */
    public function song()
    { 
        if ($this->request->isPost()) {
            $data = input();
            $goods=Db::name('goods')->where(array('goods_id'=>$data['goods_id']))->cache(true,300)->find();
            if($goods['store_count']==0){
                $this->error('库存不足');
            }
            $info = Db::name('user')->find($data['user_id']);
            $result = addMine($data['user_id'],$info['username'],$data['goods_id']);
            if($result){
                Db::name('goods')->where(array('goods_id'=>$data['goods_id']))->setDec('store_count',1);
                $this->success("操作成功！");
            } else {
                $this->error("操作失败！");
            }
        }else{
            $user_id = input('ids');
            if (!$user_id > 0)
                $this->error("参数有误");
            $rank = Db::name('goods')->select();
            $info = Db::name('user')->find($user_id);
            $this->view->assign('user_id', $user_id);
            $this->view->assign('info', $info);
            $this->view->assign('rank', $rank);
            return $this->view->fetch();
        }

    }

    /**
     * 删除或者冻结
     */
    public function delete_user()
    {
        $user_id = input('ids');
        if (!$user_id > 0)
            $this->error("参数有误");
        if ($this->request->isPost()) {
            $op = input('post.op');
            $desc = input('post.desc');
            if (!$desc){
                $this->error("请填写操作说明");
            }
            $admin_info = session('admin.username');
            $info = $this->model->where('id', $user_id)->find();
            if ($op == 1) {      //删除
                $rr = $this->model->where('id', $user_id)->delete();
                //冻结日志
                db('dong')->insert(['addtime'  => time(),
                                    'uid'      => $user_id,
                                    'username' => $info['username'],
                                    'info'     => "管理员{$admin_info}删除：{$desc}"]);
            } else {        //冻结
                $rr = $this->model->where('id', $user_id)->setField('status', '0');
                //冻结日志
                db('dong')->insert(['addtime'  => time(),
                                    'uid'      => $user_id,
                                    'username' => $info['username'],
                                    'info'     => "管理员{$admin_info}冻结：{$desc}"]);
            }
            if ($rr == 1) {
                $this->success("操作成功");
            } else {
                $this->error("操作失败");
            }
        }
        $this->view->assign('user_id', $user_id);
        return $this->view->fetch();
    }
}
