<?php

namespace addons\shop\controller;

use app\common\controller\Api;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\DbException;
use think\Log;


class Ajax extends Api
{

    protected $noNeedLogin = ['init'];
    protected $noNeedRight = '*';

    public function _initialize()
    {
        parent::_initialize();
    }
 
    /**
     * 立即购买商品
     */
    public function buy()
    {
        $id = $this->request->request('gid');
        $num = $this->request->request('goods_num');
        $spec_key_name = $this->request->request('spec_key_name');
        $spec_key = $this->request->request('spec_key');
        $this->com_add($id, $num, 1, $spec_key_name, $spec_key);
        $this->success('订单确认', '', addon_url('shop/cart/cart2'));
    }

    /**
     * 加入购物车
     */
    public function addcart()
    {
        $id = $this->request->request('gid');
        $num = $this->request->request('goods_num');
        $spec_key_name = $this->request->request('spec_key_name');
        $spec_key = $this->request->request('spec_key');
        $this->com_add($id, $num, 0, $spec_key_name, $spec_key);
        $this->success('加入成功', '', addon_url('shop/cart/cart'));
    }

    /**
     * 加入购物车 公共方法
     * @param $id
     * @param $num
     */
    protected function com_add($id, $num, $state, $spec_key_name, $spec_key)
    {
        //开启事务,避免出现垃圾数据
        Db::startTrans();
        try {
            $goods = Db::name('shop_goods')->where(array('goods_id' => $id))->find();
            if ($goods['store_count'] == 0) {
                $this->error('库存不足' . $goods['store_count']);
            }
            if ($goods['is_on_sale'] == 0) {
                $this->error('商品已下架');
            }
            $user = $this->auth->getUserinfo();
            if (!$user['address'] || !$user['mobile'] || !$user['consignee']) {
                $this->error("请完善个人信息", '', url('index/Info/editinfo2'));
            }
            Db::name('cart')->where(['user_id' => $user['id']])->setField(['selected' => 0]);
            $info = Db::name('cart')->where(['user_id' => $user['id'], 'goods_id' => $id, 'spec_key' => $spec_key])->find();
            if ($info) {
                Db::name('cart')->where(['id' => $info['id']])->setField(['goods_num' => $num, 'selected' => $state]);
            } else {
                $price = $spec_key == 0 ? $goods['shop_price'] : $spec_key;
                //增加购物车
                Db::name('cart')->insertGetId([
                    'user_id'       => $user['id'],
                    'goods_id'      => $id,
                    'goods_name'    => $goods['goods_name'],
                    'goods_num'     => $num,   //商品数量
                    'market_price'  => $goods['market_price'],
                    'goods_price'   => $price,
                    'shop_id'       => $goods['shop_id'],
                    'add_time'      => time(),
                    'selected'      => $state,
                    'spec_key_name' => $spec_key_name,   //商品规格名称
                    'spec_key'      => $spec_key,   //商品规格价格

                ]);
            }
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
    }

    /**
     * 移除购物车
     */
    public function delcart()
    {
        $id = $this->request->request('gid');
        //开启事务,避免出现垃圾数据
        Db::startTrans();
        try {
            $user = $this->auth->getUserinfo();
            Db::name('cart')->where(['user_id' => $user['id'], 'goods_id' => $id])->delete();
            Db::commit();
            $this->success('删除成功', '', addon_url('shop/cart/cart'));

        } catch (Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }

    }

    /**
     * 购物车加
     */
    public function upcart()
    {
        $id = $this->request->request('gid');
        //开启事务,避免出现垃圾数据
        Db::startTrans();
        try {
            $user = $this->auth->getUserinfo();
            Db::name('cart')->where(['user_id' => $user['id'], 'goods_id' => $id])->setInc('goods_num', 1);
            Db::commit();
            $this->success('', '', addon_url('shop/cart/cart'));

        } catch (Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }

    }

    /**
     * 购物车减
     */
    public function downcart()
    {
        $id = $this->request->request('gid');
        //开启事务,避免出现垃圾数据
        Db::startTrans();
        try {
            $user = $this->auth->getUserinfo();
            $info = Db::name('cart')->where(['user_id' => $user['id'], 'goods_id' => $id])->find();
            if ($info['goods_num'] > 1) {
                Db::name('cart')->where(['user_id' => $user['id'], 'goods_id' => $id])->setDec('goods_num', 1);
            } else {
                $this->error('数量为零');
            }
            Db::commit();
            $this->success('', '', addon_url('shop/cart/cart'));

        } catch (Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }

    }

    /**
     * 购物选择
     */
    public function chosecart()
    {
        $id = $this->request->request('gid');
        $chose = $this->request->request('chose');
        //开启事务,避免出现垃圾数据
        Db::startTrans();
        try {
            $user = $this->auth->getUserinfo();
            Db::name('cart')->where(['user_id' => $user['id'], 'goods_id' => $id])->setField('selected', $chose);
            Db::commit();
            $this->success('', '', addon_url('shop/cart/cart'));
        } catch (Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }

    }

    /**
     * 购物车 店铺选择
     */
    public function choseshop()
    {
        $id = $this->request->request('gid');
        $chose = $this->request->request('chose');
        //开启事务,避免出现垃圾数据
        Db::startTrans();
        try {
            $user = $this->auth->getUserinfo();
            Db::name('cart')->where(['user_id' => $user['id'], 'shop_id' => $id])->setField('selected', $chose);
            Db::commit();
            $this->success('', '', addon_url('shop/cart/cart'));
        } catch (Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }

    }

    /**
     * 普通商品 购物车 结算
     */
    public function suancart()
    {
        $myset = config('site');
        //开启事务,避免出现垃圾数据
        Db::startTrans();
        try {
            $walltype = config('site.walltype');
            $user = $this->auth->getUserinfo();
            // if ($user['jointime'] == 0) {
            //     $this->error('请激活账号再操作！', '', '');
            // } 
            if (empty($user['address']) || empty($user['shouphone']) || empty($user['consignee'])) {
                $this->error('请完善收货地址！', '', url('index/info/editinfo2'));
            }
            $ptype = input('ptype');                    //支付类型
            $shipping_code = input('shipping_code', '申通');    //物流id

            $list = Db::name('cart')->alias('a')
                ->join('fa_shop_goods b', 'b.goods_id = a.goods_id')
                ->where(['user_id' => $user['id'], 'selected' => 1])->select();
            //总费用
            $total = 0;
            $tmp=[];
            foreach ($list as $item) {

                $total += ($item['goods_price'] * $item['goods_num']);
                if ($item['goods_num'] > $item['store_count']) {
                    $this->error($item['goods_name'] . '库存为' . $item['store_count'] . ',请重新选择');
                }
                $tmp[$item['shop_id']][] = $item;
            }

            //扣款
            // $row = json_decode(file_get_contents('https://www.huobi.co/-/x/general/index/constituent_symbol/detail'), true);
            // $lists = $row['data']['symbols'];
            // foreach ($lists as $vo) {
            //     $binfo[$vo['symbol']] =$vo;
            // }
            // switch ($ptype){
            //     case 'wall2':    //lmc
            //         $lmcinfo = db('kline')->order('id','desc')->cache(true, 600)->find();
            //         $lmcprice = $lmcinfo['close'] * $myset['usd2cny'];
            //         $need = round($total/$lmcprice,6);
            //         break;
            //     case 'wall3':    //btc
            //         $btcprice = $binfo['btcusdt']['close']* $myset['usd2cny'];
            //         $need = round($total/$btcprice,6);
            //         break;
            //     case 'wall4':    //eth
            //         $btcprice = $binfo['ethusdt']['close']* $myset['usd2cny'];
            //         $need = round($total/$btcprice,6);
            //         break;
            //     case 'wall5':    //ltc
            //         $btcprice = $binfo['ltcusdt']['close']* $myset['usd2cny'];
            //         $need = round($total/$btcprice,6);
            //         break;
            //     case 'wall6':    //eos
            //         $btcprice = $binfo['eosusdt']['close']* $myset['usd2cny'];
            //         $need = round($total/$btcprice,6);
            //         break;
            //     default:
            //         $need = $total;
            // }
            // $needfee=$need*($myset['shopczfee']*0.01+1);
             $need = $total;
            //   判断金额是否够
            if ($user[$ptype] < $need) {
                $this->error('金额不足');
            }
            caiwu($user['id'], -$need, 5, $ptype, '购买商品'); 
            foreach ($tmp as $key => $goods) {
//                dump('店铺id'.$key);
//                dump($goods);
                unset($ordergoods);
                $orderprice = 0;
                foreach ($goods as $good) {
                    $orderprice += ($good['goods_price'] * $good['goods_num']);
                }
                //增加订单
                $ordersn = 'SHOP' . date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
                $orderid = Db::name('shop_order')->insertGetId([
                    'order_sn'      => $ordersn,
                    'user_id'       => $user['id'],
                    'consignee'     => $user['consignee'],
                    'address'       => $user['address'],
                    'mobile'        => $user['shouphone'],
                    'goods_price'   => $orderprice,
                    'total_amount'  => $orderprice,
                    'add_time'      => time(),
                    'shipping_name' => $shipping_code,   //快递
                    'order_status'  => 1,   //已付款待发货
                    'ptype'         => $ptype,
                    'shop_id'       => $key,
                    'pay_name'      => $walltype[$ptype],    //支付方式
                ]);
                foreach ($goods as $good) {
                    $ordergoods[] = [
                        'order_id'      => $orderid,
                        'goods_id'      => $good['goods_id'],
                        'goods_name'    => $good['goods_name'],
                        'goods_num'     => $good['goods_num'],   //商品数量
                        'market_price'  => $good['market_price'],
                        'goods_price'   => $good['shop_price'],
                        'cost_price'    => $good['cost_price'],
                        'spec_key_name' => $good['spec_key_name'],   //商品规格名称
                        'spec_key'      => $good['spec_key'],   //商品规格价格
                    ];
                    Db::name('shop_goods')->where(array('goods_id' => $good['goods_id']))->setDec('store_count', $good['goods_num']);   //减少库存

                }
                //增加订单商品
                Db::name('shop_order_goods')->insertAll($ordergoods);
            }

            //删除购物车
            Db::name('cart')->where(['user_id' => $user['id'], 'selected' => 1])->delete();

            Db::commit();
            $this->success('提交成功', '', url('index/user/order'));
        } catch (Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }

    }

    /**
     * 改变订单状态
     */
    public function c_order()
    {
        $id = $this->request->request('gid');
        $state = $this->request->request('state');
        //开启事务,避免出现垃圾数据
        Db::startTrans();
        try {
            $order = Db::name('shop_order')->find($id);
            //确认收货
            if ($state == '3' && $order['order_status']!='3') {
                Db::name('shop_order')->where(['order_id' => $order['order_id']])->setField(['confirm_time' => time(), 'order_status' => '3']);

                if ($order['shop_id'] != 0) {
                    caiwu($order['shop_id'], $order['total_amount'], 32, 'wall2', '卖出商品' . $order['order_sn']);

                }
            } else {
                $this->error('已经确认，请勿重复确认', '321');
            }
            Db::commit();
            $this->success('操作成功', '321');
        } catch (Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }

    }

    /**
     * 收藏商品
     */
    public function collection()
    {
        $user = $this->auth->getUserinfo();
        $gid = input('gid');
        $collect = array();
        if (empty($user['collection'])) {
            $collect[] = $gid;
            $new = $collect;
        } else {
            $new = json_decode($user['collection'], true);

            if (in_array($gid, $new)) {
                $this->error('已收藏');
            } else {
                array_push($new, $gid);
            }

        }
        $new = json_encode($new);
        $re = Db::name('user')->where('id', $user['id'])->setField('collection', $new);
        if($re){
            $this->success('收藏成功');
        }else{
            $this->error('收藏失败');
        }

    }

    /**
     * 商家发布商品
     * @return \think\response\Json
     */
    public function uploadimg()
    {
        $urls=controller('common')->upload2();
        $data['status']=1;
        $data['url']=reset($urls);
        return json($data);

    }
    /**
     * 商家发布商品
     * @return \think\response\Json
     */
    public function uploadvideo()
    {
        $urls=controller('common')->uploadvideo();
        $data['status']=1;
        $data['url']=reset($urls);
        return json($data);

    }
    /**
     * 二级密码验证
     */
    public function seccodedo()
    {
        $pass2 = input('pass2');
        $rshy = $this->auth->getUserinfo();
        if (authcode($rshy['password2'], 'DECODE') == $pass2) {
            $this->success('', 1);
        } else {
            $this->error('支付密码错误');
        }

    }

    
    /**
     * 申请开店
     */
    public function openshop()
    {

        $rshy = $this->auth->getUserinfo();
        $shopname = input('shopname');
        $shopcate = input('shopcate');
        $myset = \config('site');
 
        if ($rshy['jointime'] == 0) {
            $this->error('请激活账号再操作！', '', '');
        }
        if ($rshy['issm'] !== '1') {
            $this->error('请先进行身份认证！', '', url('index/user/realname'));
        }
        if ($shopname && $shopcate) {
            $this->model->where(array('id' => $rshy['id']))->setField(['shopcate' => $shopcate, 'shopname' => $shopname, 'shop_open' => 2]);
            $this->success('提交成功，审核中', 789);
        } else {
            $this->error('请填写资料！', '', '');
        }


    }

    /**
     *  用户上传商品
     */
    public function uploadgoods()
    {
        $data = input('post.');
        if (isset($data['spec_type'])&&is_array($data['spec_type'])) {
            foreach ($data['spec_type'] as $datum){
                if($datum==''){
                    $this->error('请输入对应价格');
                }
            }
            $data['spec_type'] = json_encode($data['spec_type']);
        }

        $up = controller('common')->upload2();
        if(!isset($up['file1'])) $this->error('商品图上传有误，商品图需要 从左到右上传');
        $data['image'] = $up['file1'];
        $data['images'] =$up['file1'];
        if(isset($up['file2']))  $data['images'] .=','.$up['file2'];
        if(isset($up['file3']))  $data['images'] .=','.$up['file3'];
        $data['goods_content'] = $this->request->param('goods_content', '', 'trim');

        $re = Db::name('shop_goods')->strict(false)->insert($data);
        if ($re) {
            $this->success('操作成功', 789);
        } else {
            $this->error('操作失败,请稍后重试');
        }
    }

    /**
     *  用户编辑商品
     */
    public function editgoods()
    {
        $data = input('post.');
        $file = $this->request->file('file');
        if (!empty($file)) {
            $data['image'] = controller('common')->upload2();
        }
        $re = Db::name('shop_goods')->update($data);
        if ($re) {
            $this->success('操作成功', '', url('index/user/shopgoods'));
        } else {
            $this->error('操作失败,请稍后重试');
        }
    }


    /**
     *  用户下架商品
     */
    public function downgoods()
    {
        $id = input('gid');
        $info = Db::name('shop_goods')->find($id);
        if ($info['is_on_sale'] != '0') {
            $re = Db::name('shop_goods')->update(['goods_id' => $id, 'is_on_sale' => 0]);
            if ($re) {
                $this->success('操作成功', 321);
            } else {
                $this->error('操作失败,请稍后重试');
            }
        } else {
            $this->error('已经下架');
        }


    }

    /**
     *  用户上架商品
     */
    public function upgoods()
    {
        $id = input('gid');
        $info = Db::name('shop_goods')->find($id);
        if ($info['is_on_sale'] != '1') {
            $re = Db::name('shop_goods')->update(['goods_id' => $id, 'is_on_sale' => 1]);
            if ($re) {
                $this->success('操作成功', 321);
            } else {
                $this->error('已经上架');
            }
        } else {
            $this->error('已经上架');
        }


    }

    /**
     *  用户删除商品
     */
    public function delgoods()
    {
        $id = input('gid');
        $info = Db::name('shop_goods')->find($id);
        if ($info['is_on_sale'] != '1') {
            $re = Db::name('shop_goods')->where('goods_id',$id)->delete();
            if ($re) {
                $this->success('操作成功', 321);
            } else {
                $this->error('操作失败');
            }
        } else {
            $this->error('操作失败');
        }


    }

    /**
     *  用户发货
     */
    public function fahuo()
    {
        $data = input('post.');
        $id = $data['id'];
        $info = Db::name('shop_order')->find($id);
        if ($info['shipping_num'] > 0) {
            $this->error('已发货，请勿重复操作');
        }
        if (empty($data['shipping_name'])) {
            $this->error('请输入物流名称');
        }
        if (empty($data['shipping_num'])) {
            $this->error('请输入快递单号');
        }
        $re = Db::name('shop_order')->where('order_id', $info['order_id'])->setField(['shipping_time' => time(), 'shipping_name' => $data['shipping_name'], 'shipping_num' => $data['shipping_num'], 'order_status' => '2']);

        if ($re) {
            $this->success('操作成功', 321);
        } else {
            $this->error('操作失败,请稍后重试');
        }
    }


    /**
     *  实体申请
     */
    public function realshop()
    {
        $data = input('post.');
        $user = $this->auth->getUserinfo();
        $urls = controller('common')->upload2();
        $data['is_real'] = 2;
        $all = array_merge($data, $urls);
        if (empty($all['shopname']) || empty($all['shopcate']) || empty($all['realname']) || empty($all['idcard']) || empty($all['idcard_1']) || empty($all['idcard_2']) || empty($all['business']) || empty($all['business2'])) {
            $this->error('请完善信息再提交');
        }
        $re = Db::name('user')->where('id', $user['id'])->update($all);
        if ($re) {
            $this->success('操作成功', 789);
        } else {
            $this->error('操作失败,请稍后重试');
        }
    }

}
