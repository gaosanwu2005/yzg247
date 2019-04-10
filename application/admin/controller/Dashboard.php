<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\Config;
use think\Db;

/**
 * 控制台
 *
 * @icon fa fa-dashboard
 * @remark 用于展示当前系统中的统计数据、统计报表及重要实时数据
 */
class Dashboard extends Backend
{

    /**
     * 查看
     */
    public function index()
    {
        $seventtime = \fast\Date::unixtime('day', -7);
        $paylist = $createlist = [];
        for ($i = 0; $i < 7; $i++)
        {
            $day = date("Y-m-d", $seventtime + ($i * 86400));
            $createlist[$day] = mt_rand(20, 200);
            $paylist[$day] = mt_rand(1, mt_rand(1, $createlist[$day]));
        }
        $hooks = config('addons.hooks');
        $uploadmode = isset($hooks['upload_config_init']) && $hooks['upload_config_init'] ? implode(',', $hooks['upload_config_init']) : 'local';
        $addonComposerCfg = ROOT_PATH . '/vendor/karsonzhang/fastadmin-addons/composer.json';
        Config::parse($addonComposerCfg, "json", "composer");
        $config = Config::get("composer");
        $addonVersion = isset($config['version']) ? $config['version'] : __('Unknown');
        $totaluser =  Db::name('user')->count();
        $daiuser =  Db::name('user')->where('jointime',0)->count();   //临时会员
        $vipuser =  Db::name('user')->where('jointime','>',0)->count();   //正式会员
        $donguser =  Db::name('user')->where('status',0)->count();   //冻结会员
        $todayusersignup =  Db::name('user')->whereTime('createtime', 'today')->count();   //今日注册会员
        $todayuserlogin =  Db::name('user')->whereTime('logintime', 'today')->count();   //今日活跃会员
        $sevendnu= Db::name('user')->whereTime('createtime', 'week')->count();    //7日注册会员
        $sevendau= Db::name('user')->whereTime('logintime', 'week')->count();    //7日活跃会员
        //平台排单总量
        $jrpad= Db::name('tgmx')->where('number', '>',0)->sum('number');
        //今日排单总量
        $todaypad =  Db::name('tgmx')->where('number', '>',0)->whereTime('addtime', 'today')->sum('number');
        //今日排单会员人数
        $todaypaduser =  Db::name('tgmx')->where('number', '>',0)->whereTime('addtime', 'today')->group('userid')->count();
        //今日提现总额
        $todayxy =  Db::name('xymx')->where('number', '>',0)->whereTime('addtime', 'today')->sum('number');
        //今日提现会员人数
        $todayxyuser =  Db::name('xymx')->where('number', '>',0)->whereTime('addtime', 'today')->group('userid')->count();

        $this->view->assign([
            'totaluser'        => $totaluser,
            'daiuser'       => $daiuser,
            'vipuser'       => $vipuser,
            'donguser' => $donguser,
            'todayuserlogin'   => $todayuserlogin,
            'todayusersignup'  => $todayusersignup,
            'todayorder'       => $todaypad,
            'totalorder'    => $jrpad,
            'todaypaduser'    => $todaypaduser,
            'todayxy'    => $todayxy,
            'todayxyuser'    => $todayxyuser,
            'sevendnu'         => $sevendnu,
            'sevendau'         => $sevendau,
            'paylist'          => $paylist,
            'createlist'       => $createlist,
            'addonversion'       => $addonVersion,
            'uploadmode'       => $uploadmode
        ]);

        return $this->view->fetch();
    }

}
