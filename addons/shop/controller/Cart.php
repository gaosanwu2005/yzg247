<?php

namespace addons\shop\controller;

use think\addons\Controller;
use think\Db;

class Cart extends Controller
{
    protected $noNeedLogin = ['login'];
    protected $noNeedRight = '*';
    protected $layout = '';

    public function _initialize()
    {
        parent::_initialize();
        \config('default_ajax_return', 'html');
        $this->assign('hyrs', $this->auth->getUserinfo());
    }

    public function _empty()
    {
        return $this->fetch();
    }

    public function cart()
    {
        $user = $this->auth->getUserinfo();
        $list = Db::table('fa_cart')->alias('a')
            ->join('fa_shop_goods b', 'b.goods_id = a.goods_id')
            ->where('a.user_id', $user['id'])->select();
        $infos = Db::table('fa_cart')
            ->alias('a')
            ->join('fa_user b', 'b.id = a.shop_id')
            ->where('a.user_id', $user['id'])->group('a.shop_id')->field('sum(a.selected) as chose,sum(a.sum) as goods,a.shop_id,b.shopname')->select();

        foreach ($infos as $info) {
            if ((int) $info['chose'] == (int) $info['goods']) {
                $shopchose[$info['shop_id']] = 1;
            } else {
                $shopchose[$info['shop_id']] = 0;
            }
            $shopname[$info['shop_id']] = $info['shopname'];
        }
        $total = 0;

        foreach ($list as $item) {
            $tmp[$item['shop_id']][] = $item;
            if ($item['selected']) {
                $total += ($item['goods_price'] * $item['goods_num']);
            }
        }
        $tmp = isset($tmp) ? $tmp : [];
        $shopchose = isset($shopchose) ? $shopchose : ['0' => 1];
        $shopname = isset($shopname) ? $shopname : ['0' => '官方旗舰店'];

        $this->assign('shopname', $shopname);
        $this->assign('list', $tmp);
        $this->assign('total', $total);
        $this->assign('shopchose', $shopchose);

        return $this->fetch();
    }

    /**
     * 购物车第二步确定页面.
     */
    public function cart2()
    {
        $user = $this->auth->getUserinfo();
        $config = config('site');
        $list = Db::table('fa_cart')->alias('a')
            ->join('fa_shop_goods b', 'b.goods_id = a.goods_id')
            ->where(['a.user_id' => $user['id'], 'a.selected' => 1])->select();
        $this->assign('list', $list);
        $express = Db::table('fa_category')->where('type', 'express')->select();
        $tmp = [];
        foreach ($express as $item) {
            $tmp[$item['id']] = $item['name'];
        }
        $total = 0;
        if ($list) {
            $expressid = explode(',', $list[0]['shipping_area_ids']);
            foreach ($list as $item) {
                if ($item['selected']) {
                    $total += ($item['goods_price'] * $item['goods_num']);
                }
            }
        } else {
            $this->error('请选择商品');
        }
        $needlmc = $total;
        // $row = json_decode(file_get_contents('https://www.huobi.co/-/x/general/index/constituent_symbol/detail'), true);
        // $lists = $row['data']['symbols'];
        // foreach ($lists as $list) {
        //     $binfo[$list['symbol']] = $list;
        // }
        // $lmcinfo = db('kline')->order('id', 'desc')->cache(true, 600)->find();

        // $lmcprice = $lmcinfo['close'] * $config['usd2cny'];
        // $needlmc = round($total / $lmcprice, 6);

        // $btcprice = $binfo['btcusdt']['close'] * $config['usd2cny'];
        // $needbtc = round($total / $btcprice, 6);

        // $ethprice = $binfo['ethusdt']['close'] * $config['usd2cny'];
        // $needeth = round($total / $ethprice, 6);

        // $ltcprice = $binfo['ltcusdt']['close'] * $config['usd2cny'];
        // $needltc = round($total / $ltcprice, 6);

        // $etcprice = $binfo['etcusdt']['close'] * $config['usd2cny'];
        // $needetc = round($total / $etcprice, 6);

        // $xrpprice = $binfo['xrpusdt']['close'] * $config['usd2cny'];
        // $needxrp = round($total / $xrpprice, 6);

        // $eosprice = $binfo['eosusdt']['close'] * $config['usd2cny'];
        // $needeos = round($total / $eosprice, 6);

        $this->assign('expressid', $expressid);
        $this->assign('total', $total);
        $this->assign('express', $tmp);
        $this->assign('needlmc', $needlmc);
        // $this->assign('needbtc', $needbtc);
        // $this->assign('needeth', $needeth);
        // $this->assign('needltc', $needltc);
        // $this->assign('needetc', $needetc);
        // $this->assign('needxrp', $needxrp);
        // $this->assign('needeos', $needeos);

        return $this->fetch();
    }
}
