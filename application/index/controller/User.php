<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use think\Config;
use think\Db;
use think\Session;

/**
 * 会员中心.
 */
class User extends Frontend
{
    protected $layout = '';
    protected $noNeedLogin = ['login', 'reg', 'getpwd', 'appdownload', 'changepwd'];
    protected $noNeedRight = ['*'];
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('User');

        $this->assign('xing', -5 * 15 + 150);
        $this->assign('sidebar', 4);
    }

    public function center()
    {
        $levels = Db::name('user_level')->select();
        $this->assign('levels', $levels);

        return $this->fetch();
    }

    public function _empty()
    {
        \config('default_ajax_return', 'html');

        return $this->fetch();
    }

    public function changepwd()
    {
        $data = input();

        $this->assign('mobile', $data['mobile']);
        $this->assign('type', $data['type']);

        return $this->fetch();
    }

    public function uploadgoods()
    {
        $category = db('category')->where('type', 'shop_goods')->select();

        $this->assign('category', $category);

        return $this->fetch();
    }

    public function index()
    {
        $price = Db::name('tzrank')->order('rprice asc')->select();
        $this->assign('price', $price);

        $user = $this->auth->getUserinfo();
        $Tgmx2 = Db::name('Tgmx2');
        $Xymx2 = Db::name('Xymx2');
        $Ppmx2 = Db::name('Ppmx2');
        $myset = config('site');
        $buytrade = [];
        $saletrade = [];
        $map['userid'] = $user['id'];
        $map['a.status'] = '0';
        $map2['userid|userid1'] = $user['id'];
        $map2['status'] = ['in', ['0', '1', '4']];
        $buyorder = $Tgmx2->alias('a')->join('fa_user b ', 'b.id= a.userid')->where($map)->order('tgid DESC')->limit(20)->select();
        $saleorder = $Xymx2->alias('a')->join('fa_user b ', 'b.id= a.userid')->where($map)->order('xyid DESC')->limit(20)->select();
        $tradeorder = $Ppmx2->where($map2)->order('ppid DESC')->limit(10)->select();
        $buylist = $Tgmx2->alias('a')->join('fa_user b ', 'b.id= a.userid')->where('a.status', '0')->order('price DESC')->limit(10)->select();
        $salelist = $Xymx2->alias('a')->join('fa_user b ', 'b.id= a.userid')->where('a.status', '0')->order('price ASC')->limit(10)->select();
        if ($buylist) {
            foreach ($buylist as $key => $item) {
                $buylist[$key]['addtime'] = date('Y-m-d H:i', $item['addtime']);
                $buylist[$key]['num'] = $item['number'] - $item['buy_number'];
                $buylist[$key]['total'] = round($buylist[$key]['num'] * $item['price'], 2);
                $buylist[$key]['buy_total'] = round($item['buy_number'] * $item['price'], 2);
            }
        }
        if ($salelist) {
            foreach ($salelist as $key2 => $item2) {
                $salelist[$key2]['addtime'] = date('Y-m-d H:i', $item2['addtime']);
                $salelist[$key2]['num'] = $item2['number'] - $item2['sale_number'];
                $salelist[$key2]['total'] = round($salelist[$key2]['num'] * $item2['price'], 2);
            }
        }

        if ($buyorder) {
            foreach ($buyorder as $key3 => $item3) {
                $buyorder[$key3]['pai'] = (int) ((time() - $item3['addtime']) / 86400);
                $buyorder[$key3]['addtime'] = date('Y-m-d H:i', $item3['addtime']);
                $buyorder[$key3]['realname'] = $user['realname'];
            }
        }
        if ($saleorder) {
            foreach ($saleorder as $key4 => $item4) {
                $saleorder[$key4]['pai'] = (int) ((time() - $item4['addtime']) / 86400);
                $saleorder[$key4]['addtime'] = date('Y-m-d H:i', $item4['addtime']);
                $saleorder[$key4]['realname'] = $user['realname'];
            }
        }
        if ($tradeorder) {
            foreach ($tradeorder as $key5 => $item5) {
                $end1 = $item5['addtime'] + $myset['zdqx'] * 3600;
                $end2 = $item5['paytime'] + $myset['zdqr'] * 3600;
                //卖家
                $tmp = [0 => '<a style="color: darkred;" href="'.url('/index/business2/dkdetail').'?ppid='.$item5['ppid'].'" >买家待付款</a>',
                        1 => '<a style="color: darkred;" href="'.url('/index/business2/dkdetail').'?ppid='.$item5['ppid'].'" >买家已付款待确认</a>',
                        2 => '已完成',
                        3 => '已取消',
                        4 => '<a style="color: darkred;" href="'.url('/index/business2/dkdetail').'?ppid='.$item5['ppid'].'" >投诉中</a>', ];
                //买入
                $tmp2 = [0 => '<a style="color: green;" href="/index/business2/skdetail?ppid='.$item5['ppid'].'" >点击付款</a>',
                         1 => '<a style="color: green;" href="'.url('/index/business2/skdetail').'?ppid='.$item5['ppid'].'" >已付款待对方确认</a>',
                         2 => '已完成',
                         3 => '已取消',
                         4 => '<a style="color: green;" href="'.url('/index/business2/skdetail').'?ppid='.$item5['ppid'].'" >投诉中</a>', ];
                //买入
                $tmp3 = [0 => '<span >剩余打款时间:</span><span style="font-size: smaller;" class="untime" data-time="'.$end1.'" data-ppid="'.$item5['ppid'].'"></span>',
                         1 => '<span >剩余确认时间:</span><span style="font-size: smaller;" class="toutime" data-time="'.$end2.'" data-ppid="'.$item5['ppid'].'"></span>',
                         2 => '已完成',
                         3 => '已取消',
                         4 => '投诉中', ];
                //卖出
                $tmp4 = [0 => '<span >剩余打款时间:</span><span style="font-size: smaller;" class="toutime" data-time="'.$end1.'" data-ppid="'.$item5['ppid'].'"></span>',
                         1 => '<span >剩余确认时间:</span><span style="font-size: smaller;" class="untime" data-time="'.$end2.'" data-ppid="'.$item5['ppid'].'"></span>',
                         2 => '已完成',
                         3 => '已取消',
                         4 => '投诉中', ];
                if ($item5['userid'] == $user['id']) {
                    $buytrade[] = [
                        'status' => $tmp2[$item5['status']],
                        'endtime' => $tmp3[$item5['status']],
                        'addtime' => date('m-d H:i', $item5['addtime']),     //匹配时间
                        //                        'tgtime'=> date('m-d H:i', $item5['tgtime']),        //排单时间
                        'number' => $item5['number'],
                        'username' => $item5['account1'],
                        'ppsn' => $item5['ppsn'],
                    ];
                } else {
                    $saletrade[] = [
                        'status' => $tmp[$item5['status']],
                        'endtime' => $tmp4[$item5['status']],
                        'addtime' => date('m-d H:i', $item5['addtime']),      //匹配时间
                        //                        'xytime'=> date('m-d H:i', $item5['xytime']),         //排单时间
                        'number' => $item5['number'],
                        'username' => $item5['account'],
                        'ppsn' => $item5['ppsn'],
                    ];
                }
            }
        }
        $this->assign('buyorder', $buyorder);  //个人买
        $this->assign('saleorder', $saleorder);  //个人卖
        $this->assign('buytrade', $buytrade);   //个人买 交易
        $this->assign('saletrade', $saletrade); //个人卖 交易
        $this->assign('buylist', $buylist);  //买家列表
        $this->assign('salelist', $salelist);  //卖家列表

        return $this->fetch();
    }

    public function login()
    {
        if ($this->auth->isLogin()) {
            $this->redirect('index/index1');   // 首页
        }
        $country = db('country')->cache(true)->select();
        $this->assign('country', $country);

        $arrGlobals['country'] = array();
        $arrGlobals['country']['CN'] = '中国';
        $arrGlobals['country']['AL'] = '阿尔巴尼亚 (Shqipëria)';
        $arrGlobals['country']['DZ'] = '阿尔及利亚 (الجمهورية الجزائرية)';
        $arrGlobals['country']['AF'] = '阿富汗 (افغانستان)';
        $arrGlobals['country']['AR'] = '阿根廷 (Argentina)';
        $arrGlobals['country']['AE'] = '阿拉伯联合酋长国 (الإمارات العربيّة المتّحد)';
        $arrGlobals['country']['AW'] = '阿鲁巴 (Aruba)';
        $arrGlobals['country']['OM'] = '阿曼 (عمان)';
        $arrGlobals['country']['AZ'] = '阿塞拜疆 (Azərbaycan)';
        $arrGlobals['country']['EG'] = '埃及 (مصر)';
        $arrGlobals['country']['ET'] = "埃塞俄比亚 (Ityop'iya)";
        $arrGlobals['country']['IE'] = '爱尔兰 (Ireland)';
        $arrGlobals['country']['EE'] = '爱沙尼亚 (Eesti)';
        $arrGlobals['country']['AD'] = '安道尔 (Andorra)';
        $arrGlobals['country']['AO'] = '安哥拉 (Angola)';
        $arrGlobals['country']['AI'] = '安圭拉 (Anguilla)';
        $arrGlobals['country']['AG'] = '安提瓜和巴布达 (Antigua and Barbuda)';
        $arrGlobals['country']['AT'] = '奥地利 (Österreich)';
        $arrGlobals['country']['AU'] = '澳大利亚 (Australia)';
        $arrGlobals['country']['BB'] = '巴巴多斯 (Barbados)';
        $arrGlobals['country']['PG'] = '巴布亚新几内亚 (Papua New Guinea)';
        $arrGlobals['country']['BS'] = '巴哈马 (Bahamas)';
        $arrGlobals['country']['PK'] = '巴基斯坦 (پاکستان)';
        $arrGlobals['country']['PY'] = '巴拉圭 (Paraguay)';
        $arrGlobals['country']['PS'] = '巴勒斯坦 (Palestine)';
        $arrGlobals['country']['BH'] = '巴林 (بحرين)';
        $arrGlobals['country']['PA'] = '巴拿马 (Panamá)';
        $arrGlobals['country']['BR'] = '巴西 (Brasil)';
        $arrGlobals['country']['BY'] = '白俄罗斯 (Белару́сь)';
        $arrGlobals['country']['BM'] = '百慕大 (Bermuda)';
        $arrGlobals['country']['BG'] = '保加利亚 (България)';
        $arrGlobals['country']['MP'] = '北马里亚纳群岛 (Northern Mariana Islands)';
        $arrGlobals['country']['BJ'] = '贝宁 (Bénin)';
        $arrGlobals['country']['BE'] = '比利时 (België)';
        $arrGlobals['country']['IS'] = '冰岛 (Ísland)';
        $arrGlobals['country']['BO'] = '玻利维亚 (Bolivia)';
        $arrGlobals['country']['PR'] = '波多黎各 (Puerto Rico)';
        $arrGlobals['country']['PL'] = '波兰 (Polska)';
        $arrGlobals['country']['BA'] = '波斯尼亚和黑塞哥维那 (Bosna i Hercegovina)';
        $arrGlobals['country']['BW'] = '博茨瓦纳 (Botswana)';
        $arrGlobals['country']['BZ'] = '伯利兹 (Belize)';
        $arrGlobals['country']['BT'] = '不丹 (འབྲུག་ཡུལ)';
        $arrGlobals['country']['BF'] = '布基纳法索 (Burkina Faso)';
        $arrGlobals['country']['BI'] = '布隆迪 (Uburundi)';
        $arrGlobals['country']['BV'] = '布韦岛 (Bouvet Island)';
        $arrGlobals['country']['KP'] = '朝鲜 (조선)';
        $arrGlobals['country']['GQ'] = '赤道几内亚 (Guinea Ecuatorial)';
        $arrGlobals['country']['DK'] = '丹麦 (Danmark)';
        $arrGlobals['country']['DE'] = '德国 (Deutschland)';
        $arrGlobals['country']['TL'] = '东帝汶 (Timor-Leste)';
        $arrGlobals['country']['TG'] = '多哥 (Togo)';
        $arrGlobals['country']['DO'] = '多米尼加共和国 (Dominican Republic)';
        $arrGlobals['country']['DM'] = '多米尼克 (Dominica)';
        $arrGlobals['country']['RU'] = '俄罗斯 (Россия)';
        $arrGlobals['country']['EC'] = '厄瓜多尔 (Ecuador)';
        $arrGlobals['country']['ER'] = '厄立特里亚 (Ertra)';
        $arrGlobals['country']['FR'] = '法国 (France)';
        $arrGlobals['country']['FO'] = '法罗群岛 (Faroe Islands)';
        $arrGlobals['country']['PF'] = '法属波利尼西亚 (French Polynesia)';
        $arrGlobals['country']['GF'] = '法属圭亚那 (French Guiana)';
        $arrGlobals['country']['TF'] = '法属南部领地 (French Southern Territories)';
        $arrGlobals['country']['PH'] = '菲律宾 (Pilipinas)';
        $arrGlobals['country']['FI'] = '芬兰 (Suomi)';
        $arrGlobals['country']['CV'] = '佛得角 (Cabo Verde)';
        $arrGlobals['country']['AX'] = '福克兰群岛 (Åland Islands)';
        $arrGlobals['country']['FK'] = '福克兰群岛 (Falkland Islands)';
        $arrGlobals['country']['GM'] = '冈比亚 (Gambia)';
        $arrGlobals['country']['CG'] = '刚果 (Congo)';
        $arrGlobals['country']['CD'] = '刚果民主共和国 (Congo, Democratic Republic of the)';
        $arrGlobals['country']['CO'] = '哥伦比亚 (Colombia)';
        $arrGlobals['country']['CR'] = '哥斯达黎加 (Costa Rica)';
        $arrGlobals['country']['GG'] = '格恩西岛 (Guernsey)';
        $arrGlobals['country']['GD'] = '格林纳达 (Grenada)';
        $arrGlobals['country']['GL'] = '格陵兰 (Greenland)';
        $arrGlobals['country']['GE'] = '格鲁吉亚 (საქართველო)';
        $arrGlobals['country']['CU'] = '古巴 (Cuba)';
        $arrGlobals['country']['GP'] = '瓜德罗普 (Guadeloupe)';
        $arrGlobals['country']['GU'] = '关岛 (Guam)';
        $arrGlobals['country']['GY'] = '圭亚那 (Guyana)';
        $arrGlobals['country']['KZ'] = '哈萨克斯坦 (Қазақстан)';
        $arrGlobals['country']['HT'] = '海地 (Haïti)';
        $arrGlobals['country']['KR'] = '韩国 (한국)';
        $arrGlobals['country']['NL'] = '荷兰 (Nederland)';
        $arrGlobals['country']['AN'] = '荷属安的列斯 (Netherlands Antilles)';
        $arrGlobals['country']['HM'] = '赫德和麦克唐纳群岛 (Heard Island and McDonald Islands)';
        $arrGlobals['country']['HN'] = '洪都拉斯 (Honduras)';
        $arrGlobals['country']['KI'] = '基里巴斯 (Kiribati)';
        $arrGlobals['country']['DJ'] = '吉布提 (Djibouti)';
        $arrGlobals['country']['KG'] = '吉尔吉斯斯坦 (Кыргызстан)';
        $arrGlobals['country']['GN'] = '几内亚 (Guinée)';
        $arrGlobals['country']['GW'] = '几内亚比绍 (Guiné-Bissau)';
        $arrGlobals['country']['CA'] = '加拿大 (Canada)';
        $arrGlobals['country']['GH'] = '加纳 (Ghana)';
        $arrGlobals['country']['GA'] = '加蓬 (Gabon)';
        $arrGlobals['country']['KH'] = '柬埔寨 (Kampuchea)';
        $arrGlobals['country']['CZ'] = '捷克共和国 (Česko)';
        $arrGlobals['country']['ZW'] = '津巴布韦 (Zimbabwe)';
        $arrGlobals['country']['CM'] = '喀麦隆 (Cameroun)';
        $arrGlobals['country']['QA'] = '卡塔尔 (قطر)';
        $arrGlobals['country']['KY'] = '开曼群岛 (Cayman Islands)';
        $arrGlobals['country']['CC'] = '科科斯群岛 (Cocos Islands)';
        $arrGlobals['country']['KM'] = '科摩罗 (Comores)';
        $arrGlobals['country']['CI'] = "科特迪瓦 (Côte d'Ivoire)";
        $arrGlobals['country']['KW'] = '科威特 (الكويت)';
        $arrGlobals['country']['HR'] = '克罗地亚 (Hrvatska)';
        $arrGlobals['country']['KE'] = '肯尼亚 (Kenya)';
        $arrGlobals['country']['CK'] = '库克群岛 (Cook Islands)';
        $arrGlobals['country']['LV'] = '拉脱维亚 (Latvija)';
        $arrGlobals['country']['LS'] = '莱索托 (Lesotho)';
        $arrGlobals['country']['LA'] = '老挝 (ນລາວ)';
        $arrGlobals['country']['LB'] = '黎巴嫩 (لبنان)';
        $arrGlobals['country']['LR'] = '利比里亚 (Liberia)';
        $arrGlobals['country']['LY'] = '利比亚 (ليبية)';
        $arrGlobals['country']['LT'] = '立陶宛 (Lietuva)';
        $arrGlobals['country']['LI'] = '列支敦士登 (Liechtenstein)';
        $arrGlobals['country']['RE'] = '留尼汪岛 (Reunion)';
        $arrGlobals['country']['LU'] = '卢森堡 (Lëtzebuerg)';
        $arrGlobals['country']['RW'] = '卢旺达 (Rwanda)';
        $arrGlobals['country']['RO'] = '罗马尼亚 (România)';
        $arrGlobals['country']['MG'] = '马达加斯加 (Madagasikara)';
        $arrGlobals['country']['MT'] = '马耳他 (Malta)';
        $arrGlobals['country']['MV'] = '马尔代夫 (ގުޖޭއްރާ ޔާއްރިހޫމްޖ)';
        $arrGlobals['country']['MW'] = '马拉维 (Malawi)';
        $arrGlobals['country']['MY'] = '马来西亚 (Malaysia)';
        $arrGlobals['country']['ML'] = '马里 (Mali)';
        $arrGlobals['country']['MK'] = '马其顿 (Македонија)';
        $arrGlobals['country']['MH'] = '马绍尔群岛 (Marshall Islands)';
        $arrGlobals['country']['MQ'] = '马提尼克 (Martinique)';
        $arrGlobals['country']['YT'] = '马约特岛 (Mayotte)';
        $arrGlobals['country']['MU'] = '毛里求斯 (Mauritius)';
        $arrGlobals['country']['MR'] = '毛里塔尼亚 (موريتانية)';
        $arrGlobals['country']['US'] = '美国 (United States)';
        $arrGlobals['country']['AS'] = '美属萨摩亚 (American Samoa)';
        $arrGlobals['country']['UM'] = '美属外岛 (United States minor outlying islands)';
        $arrGlobals['country']['MN'] = '蒙古 (Монгол Улс)';
        $arrGlobals['country']['MS'] = '蒙特塞拉特 (Montserrat)';
        $arrGlobals['country']['BD'] = '孟加拉 (বাংলাদেশ)';
        $arrGlobals['country']['PE'] = '秘鲁 (Perú)';
        $arrGlobals['country']['FM'] = '密克罗尼西亚 (Micronesia)';
        $arrGlobals['country']['MM'] = '缅甸 (Լեռնային Ղարաբաղ)';
        $arrGlobals['country']['MD'] = '摩尔多瓦 (Moldova)';
        $arrGlobals['country']['MA'] = '摩洛哥 (مغرب)';
        $arrGlobals['country']['MC'] = '摩纳哥 (Monaco)';
        $arrGlobals['country']['MZ'] = '莫桑比克 (Moçambique)';
        $arrGlobals['country']['MX'] = '墨西哥 (México)';
        $arrGlobals['country']['NA'] = '纳米比亚 (Namibia)';
        $arrGlobals['country']['ZA'] = '南非 (South Africa)';
        $arrGlobals['country']['AQ'] = '南极洲 (Antarctica)';
        $arrGlobals['country']['GS'] = '南乔治亚和南桑德威奇群岛 (South Georgia and the South Sandwich Islands)';
        $arrGlobals['country']['NP'] = '尼泊尔 (नेपाल)';
        $arrGlobals['country']['NI'] = '尼加拉瓜 (Nicaragua)';
        $arrGlobals['country']['NE'] = '尼日尔 (Niger)';
        $arrGlobals['country']['NG'] = '尼日利亚 (Nigeria)';
        $arrGlobals['country']['NU'] = '纽埃 (Niue)';
        $arrGlobals['country']['NO'] = '挪威 (Norge)';
        $arrGlobals['country']['NF'] = '诺福克岛 (Norfolk Island)';
        $arrGlobals['country']['PW'] = '帕劳 (Belau)';
        $arrGlobals['country']['PN'] = '皮特凯恩 (Pitcairn)';
        $arrGlobals['country']['PT'] = '葡萄牙 (Portugal)';
        $arrGlobals['country']['JP'] = '日本';
        $arrGlobals['country']['SE'] = '瑞典 (Sverige)';
        $arrGlobals['country']['CH'] = '瑞士 (Schweiz)';
        $arrGlobals['country']['SV'] = '萨尔瓦多 (El Salvador)';
        $arrGlobals['country']['WS'] = '萨摩亚 (Samoa)';
        $arrGlobals['country']['CS'] = '塞尔维亚及蒙蒂纳哥 (Србија и Црна Гора)';
        $arrGlobals['country']['SL'] = '塞拉利昂 (Sierra Leone)';
        $arrGlobals['country']['SN'] = '塞内加尔 (Sénégal)';
        $arrGlobals['country']['CY'] = '塞浦路斯 (Κυπρος)';
        $arrGlobals['country']['SC'] = '塞舌尔 (Seychelles)';
        $arrGlobals['country']['SA'] = '沙特阿拉伯 (العربية السعودية)';
        $arrGlobals['country']['CX'] = '圣诞岛 (Christmas Island)';
        $arrGlobals['country']['ST'] = '圣多美和普林西比 (São Tomé and Príncipe)';
        $arrGlobals['country']['SH'] = '圣赫勒拿 (Saint Helena)';
        $arrGlobals['country']['KN'] = '圣基茨和尼维斯 (Saint Kitts and Nevis)';
        $arrGlobals['country']['LC'] = '圣卢西亚 (Saint Lucia)';
        $arrGlobals['country']['SM'] = '圣马力诺 (San Marino)';
        $arrGlobals['country']['PM'] = '圣皮埃尔和密克隆群岛 (Saint Pierre and Miquelon)';
        $arrGlobals['country']['VC'] = '圣文森特和格林纳丁斯 (Saint Vincent and the Grenadines)';
        $arrGlobals['country']['LK'] = '斯里兰卡 (Sri Lanka)';
        $arrGlobals['country']['SK'] = '斯洛伐克 (Slovensko)';
        $arrGlobals['country']['SI'] = '斯洛文尼亚 (Slovenija)';
        $arrGlobals['country']['SJ'] = '斯瓦尔巴和扬马延 (Svalbard and Jan Mayen)';
        $arrGlobals['country']['SZ'] = '斯威士兰 (Swaziland)';
        $arrGlobals['country']['SD'] = '苏丹 (السودان)';
        $arrGlobals['country']['SR'] = '苏里南 (Suriname)';
        $arrGlobals['country']['SO'] = '索马里 (Soomaaliya)';
        $arrGlobals['country']['SB'] = '所罗门群岛 (Solomon Islands)';
        $arrGlobals['country']['TJ'] = '塔吉克斯坦 (Тоҷикистон)';
        $arrGlobals['country']['TH'] = '泰国 (ราชอาณาจักรไทย)';
        $arrGlobals['country']['TZ'] = '坦桑尼亚 (Tanzania)';
        $arrGlobals['country']['TO'] = '汤加 (Tonga)';
        $arrGlobals['country']['TC'] = '特克斯和凯科斯群岛 (Turks and Caicos Islands)';
        $arrGlobals['country']['TT'] = '特立尼达和多巴哥 (Trinidad and Tobago)';
        $arrGlobals['country']['TN'] = '突尼斯 (تونس)';
        $arrGlobals['country']['TV'] = '图瓦卢 (Tuvalu)';
        $arrGlobals['country']['TR'] = '土耳其 (Türkiye)';
        $arrGlobals['country']['TM'] = '土库曼斯坦 (Türkmenistan)';
        $arrGlobals['country']['TK'] = '托克劳 (Tokelau)';
        $arrGlobals['country']['WF'] = '瓦利斯和福图纳 (Wallis and Futuna)';
        $arrGlobals['country']['VU'] = '瓦努阿图 (Vanuatu)';
        $arrGlobals['country']['GT'] = '危地马拉 (Guatemala)';
        $arrGlobals['country']['VI'] = '维尔京群岛，美属 (Virgin Islands, U.S.)';
        $arrGlobals['country']['VG'] = '维尔京群岛，英属 (Virgin Islands, British)';
        $arrGlobals['country']['VE'] = '委内瑞拉 (Venezuela)';
        $arrGlobals['country']['BN'] = '文莱 (Brunei Darussalam)';
        $arrGlobals['country']['UG'] = '乌干达 (Uganda)';
        $arrGlobals['country']['UA'] = '乌克兰 (Україна)';
        $arrGlobals['country']['UY'] = '乌拉圭 (Uruguay)';
        $arrGlobals['country']['UZ'] = "乌兹别克斯坦 (O'zbekiston)";
        $arrGlobals['country']['ES'] = '西班牙 (España)';
        $arrGlobals['country']['EH'] = '西撒哈拉 (صحراوية)';
        $arrGlobals['country']['GR'] = "希腊 ('Eλλας)";
        $arrGlobals['country']['SG'] = '新加坡 (Singapura)';
        $arrGlobals['country']['NC'] = '新喀里多尼亚 (New Caledonia)';
        $arrGlobals['country']['NZ'] = '新西兰 (New Zealand)';
        $arrGlobals['country']['HU'] = '匈牙利 (Magyarország)';
        $arrGlobals['country']['SY'] = '叙利亚 (سورية)';
        $arrGlobals['country']['JM'] = '牙买加 (Jamaica)';
        $arrGlobals['country']['AM'] = '亚美尼亚 (Հայաստան)';
        $arrGlobals['country']['YE'] = '也门 (اليمن)';
        $arrGlobals['country']['IQ'] = '伊拉克 (العراق)';
        $arrGlobals['country']['IR'] = '伊朗 (ایران)';
        $arrGlobals['country']['IL'] = '以色列 (ישראל)';
        $arrGlobals['country']['IT'] = '意大利 (Italia)';
        $arrGlobals['country']['IN'] = '印度 (India)';
        $arrGlobals['country']['ID'] = '印度尼西亚 (Indonesia)';
        $arrGlobals['country']['GB'] = '英国 (United Kingdom)';
        $arrGlobals['country']['IO'] = '英属印度洋领地 (British Indian Ocean Territory)';
        $arrGlobals['country']['JO'] = '约旦 (الارد)';
        $arrGlobals['country']['VN'] = '越南 (Việt Nam)';
        $arrGlobals['country']['ZM'] = '赞比亚 (Zambia)';
        $arrGlobals['country']['JE'] = '泽西岛 (Jersey)';
        $arrGlobals['country']['TD'] = '乍得 (Tchad)';
        $arrGlobals['country']['GI'] = '直布罗陀 (Gibraltar)';
        $arrGlobals['country']['CL'] = '智利 (Chile)';
        $arrGlobals['country']['CF'] = '中非共和国 (République Centrafricaine)';

        $arrGlobals['country']['NR'] = '瑙鲁 (Naoero)';
        $arrGlobals['country']['VA'] = '梵蒂冈 (Città del Vaticano)';
        $arrGlobals['country']['FJ'] = '斐济 (Fiji)';
//        $this->assign('country',$arrGlobals['country']);

        return $this->fetch();
    }

    /**
     * 我的直推.
     */
    public function wdzt()
    {
        $account = input('post.account');
        $users = $this->model;
        $rshy = $this->auth->getUser();
        $map['tjuser'] = array('eq', $rshy['username']);
        if ($account != '') {
            $map['username'] = $account;
        }
        $list = $users->where($map)->order('id desc')->paginate(10);
        $this->assign('page', $list->render());
        $this->assign('list', $list);

        return $this->fetch();
    }

    /**
     * 我的团队
     */
    public function numteam()
    {
        $user = $this->auth->getUser();

        //我的总算力
        $sumsl = db('order')->where(array('uid' => $user['id'], 'status' => 1))->sum('kjsl');
        db('user')->where(array('id' => $user['id']))->setField('slrate', $sumsl);
        $xia = db('user')->where("find_in_set({$user['id']},tpath)")->sum('slrate');
        $xia = isset($xia) ? $xia : 0;
        $user = $this->auth->getUser();
        if ($xia != $user['tdsl']) {
            $nn = $xia;
            db('user')->where(array('id' => $user['id']))->setField('tdsl', $nn);   //更新队长社区算力
        }

        $yd = db('user')->where(array('tjid' => $user['id']))->select();
        $ed = team($user['id'], 1);
        $sd = team($user['id'], 2);

        $this->assign('yd', $yd);
        $this->assign('ed', $ed);
        $this->assign('sd', $sd);

        $levels = Db::table('fa_user_level')->cache(true, 60)->select();
        foreach ($levels as $level) {
            $tmp[$level['level_id']] = $level['level_name'];
        }

        $this->assign('listtext', $tmp);
        $this->assign('list', $user);

        $path = explode(',', $user['tpath']);
        if (isset($path[1])) {
            $team = db('war_team')->where(array('user_id' => $path[1]))->find();
            if (isset($team['id'])) {
                $this->assign('team', $team);
            }
        } else {
            $team = db('war_team')->where(array('user_id' => $user['id']))->find();
            if (isset($team['id'])) {
                $this->assign('team', $team);
            }
        }

        return $this->fetch();
    }

    /**
     * 推广二维码
     *Create by xiaoniu.
     */
    public function sendlink()
    {
        $rshy = $this->auth->getUser();
//        $mylink =  "www.yjxgf.com/third/connect/wechat?tgno={$rshy['tgno']}";   //微信授权
//        $mylink =  $_SERVER['HTTP_HOST'] . "/index.php/index/user/reg?tgno={$rshy['tgno']}";
        $mylink = $_SERVER['HTTP_HOST']."/index/index/reg?tgno={$rshy['tgno']}";
        $this->assign('mylink', $mylink);

        return $this->fetch();
    }

    /**
     * 推广注册
     *Create by xiaoniu.
     */
    public function reg()
    {
        $tjuser = input('get.tjuser');
//        if ($tjuser == '') {
//            $this->redirect('index/user/login');
//        }
        $country = db('country')->cache(true)->select();
        $this->assign('country', $country);
        $this->assign('tjuser', $tjuser);

        return $this->fetch();
    }

    /**
     * 找回密码
     *Create by xiaoniu.
     */
    public function getpwd()
    {
        $country = db('country')->cache(true)->select();
        $this->assign('country', $country);

        return $this->fetch();
    }

    /**
     * 找回密码2
     *Create by xiaoniu.
     */
    public function getpwd2()
    {
        $country = db('country')->cache(true)->select();
        $this->assign('country', $country);

        return $this->fetch();
    }

    /**
     * 我的矿机
     *Create by xiaoniu.
     */
    public function mills()
    {
        $info = $this->auth->getUser();
        $list = db('order')->alias('a')->join('fa_goods b', 'b.goods_id = a.gid')->where(array('uid' => $info['id']))->select();
        $this->assign('list', $list);

        //我的总算力
        $sumsl = db('order')->where(array('uid' => $info['id'], 'status' => 1))->sum('kjsl');
        db('user')->where(array('id' => $info['id']))->setField('slrate', $sumsl);
        $xia = db('user')->where("find_in_set({$info['id']},tpath)")->sum('slrate');
        $xia = isset($xia) ? $xia : 0;
        $user = $this->auth->getUser();
        if ($xia != $user['tdsl']) {
            $nn = $xia;
            db('user')->where(array('id' => $info['id']))->setField('tdsl', $nn);   //更新队长社区算力
        }

        return $this->fetch();
    }

    /**
     * 代理级别
     *Create by xiaoniu.
     */
    public function agent()
    {
        $list = db('user_level')->where('level_id>1')->select();
        $this->assign('level', $list);

        return $this->fetch();
    }

    /**
     * 入驻商家
     *Create by xiaoniu.
     */
    public function openshop()
    {
        $list = db('category')->where(['type' => 'shop_goods', 'pid' => 0])->select();
        $this->assign('cat', $list);

        return $this->fetch();
    }

    /**
     * 商城订单.
     */
    public function order()
    {
        $user = $this->auth->getUserinfo();
        $list = Db::table('fa_shop_order')->alias('a')
            ->join('fa_shop_order_goods b', 'b.order_id = a.order_id')
            ->join('fa_shop_goods c', 'c.goods_id = b.goods_id')
            ->where(['a.user_id' => $user['id']])->order('a.order_id desc')->select();

        foreach ($list as $item) {
            $tmp[$item['order_sn']][] = $item;
        }
        $tmp = isset($tmp) ? $tmp : [];
        $order_status = ['0' => '待付款', '1' => '已付款待发货', '2' => '已发货待确认', '3' => '已完成', '4' => '已完成', '5' => '已取消'];
        $this->assign('order_status', $order_status);
        $this->assign('list', $tmp);

        return $this->fetch();
    }

    /**
     * 商城卖单.
     */
    public function shoporder()
    {
        $user = $this->auth->getUserinfo();
        $list = Db::table('fa_shop_order')->alias('a')
            ->join('fa_shop_order_goods b', 'b.order_id = a.order_id')
            ->join('fa_shop_goods c', 'c.goods_id = b.goods_id')
            ->where(['a.shop_id' => $user['id']])->order('a.order_id desc')->select();

        $order_status = ['0' => '待付款', '1' => '已付款待发货', '2' => '已发货待确认', '3' => '已完成', '4' => '已完成', '5' => '已取消'];
        $this->assign('order_status', $order_status);
        $this->assign('list', $list);

        return $this->fetch();
    }

    /**
     * 店铺订单
     *Create by xiaoniu.
     */
    public function sendlink4()
    {
        $id = input('order_id');
        $order = Db::table('fa_shop_order')->alias('a')
            ->join('fa_shop_order_goods b', 'b.order_id = a.order_id')
            ->join('fa_shop_goods c', 'c.goods_id = b.goods_id')
            ->where(['a.order_id' => $id])->order('a.order_id desc')->find();

        $this->assign('order', $order);

        return $this->fetch();
    }

    /**
     * 商城C店铺查看商品
     */
    public function shopgoods()
    {
        $user = $this->auth->getUserinfo();
        $list = Db::table('fa_shop_goods')
            ->where(['shop_id' => $user['id']])->order('goods_id desc')->select();
        dump($list);
        $this->assign('list', $list);

        return $this->fetch();
    }

    /**
     * 商城C店铺编辑商品
     */
    public function editgoods()
    {
        $user = $this->auth->getUserinfo();
        $good_id = input('gid');
        $list = Db::table('fa_shop_goods')->find($good_id);
        $this->assign('list', $list);

        return $this->fetch();
    }

    /**
     * 实体申请.
     */
    public function realshop()
    {
        $list = db('category')->where(['type' => 'shop_goods', 'pid' => 0])->select();
        $this->assign('cat', $list);

        return $this->fetch();
    }

    /**
     * 矿机运行详情
     *Create by xiaoniu.
     */
    public function mymill()
    {
        $oid = input('get.oid');
        $info = $this->auth->getUser();
        $order = db('order')->where(array('id' => $oid, 'uid' => $info['id']))->find();
        if (!$order) {
            $this->error('矿机不存在');
        }
        //运行矿机
        if ($order['status'] == 0) {
            unset($data);
            $data['status'] = 1;
            $data['gettime'] = time();
            $data['starttime'] = date('Y-m-d H:i:s');
            $data['endtime'] = date('Y-m-d H:i:s', strtotime('+24 hours'));
            db('order')->where(array('id' => $oid))->update($data);
        }
        $this->assign('info', $order);

        //我的总收益，我的总算力
        $sumyf = db('order')->where(array('uid' => $info['id']))->sum('yfprice');
        $sumsl = db('order')->where(array('uid' => $info['id'], 'status' => 1))->sum('kjsl');
        db('user')->where(array('id' => $info['id']))->setField('slrate', $sumsl);
        $xia = db('user')->where("find_in_set({$info['id']},tpath)")->sum('slrate');
        $xia = isset($xia) ? $xia : 0;
        $user = $this->auth->getUser();
//        if ($user['slrate'] + $xia != $user['tdsl']) {
//            $nn = $user['slrate'] + $xia;
//            db('user')->where(array('id' => $info['id']))->setField('tdsl', $nn);   //更新队长社区算力
//        }
        if ($xia != $user['tdsl']) {
            $nn = $xia;
            db('user')->where(array('id' => $info['id']))->setField('tdsl', $nn);   //更新队长社区算力
        }
        update_user_tui($info['id']);
        $this->assign('sumyf', $sumyf);
        $this->assign('sumsl', $sumsl);
        $this->assign('yxtime', time());

        return $this->fetch();
    }

    public function coinexchange()
    {
//        $contents=Cache('getDataInfo');
        $btcinfo = Cache('btcinfo');
        $ethinfo = Cache('ethinfo');
        $ltcinfo = Cache('ltcinfo');
        $etcinfo = Cache('etcinfo');
        $xrpinfo = Cache('xrpinfo');

        $config = config('site');
        //火币网 虚拟币行情 btcusdt/eosusdt/ethusdt/xrpusdt/bchusdt/htusdt/ltcusdt/etcusdt/ontusdt/iostusdt/
//        ["btcusdt"] => array(6) {
//        ["close"] => float(4049.59)
//        ["name"] => string(3) "btc"
//        ["open"] => float(3781.58)
//        ["rise_percent"] => float(0.0709)
//        ["symbol"] => string(7) "btcusdt"
//        ["weight"] => float(0.3408447161)
        $row = json_decode(file_get_contents('https://www.huobi.co/-/x/general/index/constituent_symbol/detail'), true);
        $lists = $row['data']['symbols'];
        foreach ($lists as $list) {
            $binfo[$list['symbol']] = $list;
        }

        $lmcinfo = db('kline')->order('id', 'desc')->cache(true, 600)->find();
        $lmc['usdt'] = $lmcinfo['close'];
        $lmc['price'] = $lmcinfo['close'] * $config['usd2cny'];
        $lmcup = round(($lmcinfo['close'] - $lmcinfo['open']) / $lmcinfo['open'], 2);
        $lmc['changePercentage'] = $lmcup > 0 ? '+'.$lmcup : '-'.$lmcup;

//        if(!$contents){
//            $contents = json_decode(file_get_contents('https://www.okcoin.com/v2/spot/markets/tickers?'.mt_rand()),true);
//            Cache('getDataInfo',$contents,10);
//        }

        //btc
        if (!$btcinfo) {
            $btcinfo = json_decode(file_get_contents('https://api.huobipro.com/market/detail?symbol=btcusdt'), true);
            Cache('btcinfo', $btcinfo, 100);
        }
        $btc['price'] = $binfo['btcusdt']['close'] * $config['usd2cny'];
        $btc['usdt'] = $binfo['btcusdt']['close'];
        $btc['amount'] = round($btcinfo['tick']['amount'], 4); //24小时
        $btc['changePercentage'] = $binfo['btcusdt']['rise_percent'] * 100;
        $btc['num'] = round($binfo['btcusdt']['close'] / $lmcinfo['close'], 4);

        //eth
        if (!$ethinfo) {
            $ethinfo = json_decode(file_get_contents('https://api.huobipro.com/market/detail?symbol=ethusdt'), true);
            Cache('ethinfo', $ethinfo, 100);
        }
        $eth['price'] = $binfo['ethusdt']['close'] * $config['usd2cny'];
        $eth['usdt'] = $binfo['ethusdt']['close'];
        $eth['amount'] = round($ethinfo['tick']['amount'], 4); //24小时
        $eth['changePercentage'] = $binfo['ethusdt']['rise_percent'] * 100;
        $eth['num'] = round($binfo['ethusdt']['close'] / $lmcinfo['close'], 4);

        //ltc
        if (!$ltcinfo) {
            $ltcinfo = json_decode(file_get_contents('https://api.huobipro.com/market/detail?symbol=ltcusdt'), true);
            Cache('ltcinfo', $ltcinfo, 100);
        }
        $ltc['price'] = $binfo['ltcusdt']['close'] * $config['usd2cny'];
        $ltc['amount'] = round($ltcinfo['tick']['amount'], 4); //24小时
        $ltc['changePercentage'] = $binfo['ltcusdt']['rise_percent'] * 100;
        $ltc['num'] = round($binfo['ltcusdt']['close'] / $lmcinfo['close'], 4);

        //etc
        if (!$etcinfo) {
            $etcinfo = json_decode(file_get_contents('https://api.huobipro.com/market/detail?symbol=etcusdt'), true);
            Cache('etcinfo', $etcinfo, 100);
        }
        $etc['price'] = $binfo['etcusdt']['close'] * $config['usd2cny'];
        $etc['amount'] = round($etcinfo['tick']['amount'], 4); //24小时
        $etc['changePercentage'] = $binfo['etcusdt']['rise_percent'] * 100;
        $etc['num'] = round($binfo['etcusdt']['close'] / $lmcinfo['close'], 4);

        //xrp
        if (!$xrpinfo) {
            $xrpinfo = json_decode(file_get_contents('https://api.huobipro.com/market/detail?symbol=xrpusdt'), true);
            Cache('xrpinfo', $xrpinfo, 100);
        }
        $xrp['price'] = $binfo['xrpusdt']['close'] * $config['usd2cny'];
        $xrp['amount'] = round($xrpinfo['tick']['amount'], 4); //24小时
        $xrp['changePercentage'] = $binfo['xrpusdt']['rise_percent'];
        $xrp['num'] = round($binfo['xrpusdt']['close'] / $lmcinfo['close'], 4);

        //eos
        $eos['price'] = $binfo['eosusdt']['close'] * $config['usd2cny'];
        $eos['amount'] = round($xrpinfo['tick']['amount'] * 0.618, 4); //24小时
        $eos['changePercentage'] = $binfo['eosusdt']['rise_percent'] * 100;
        $eos['num'] = round($binfo['eosusdt']['close'] / $lmcinfo['close'], 4);

        $this->assign('lmc', $lmc);
        $this->assign('btc', $btc);
        $this->assign('eth', $eth);
        $this->assign('ltc', $ltc);
        $this->assign('etc', $etc);
        $this->assign('xrp', $xrp);
        $this->assign('eos', $eos);

        return $this->fetch();

//        if($contents){
//            $this->assign('binfo',$contents['data']);
//            return $this->fetch();
//        }else{
//            $this->error('获取失败');
//        }
    }

    public function withdraw()
    {
//        $key = input('bname');
        $config = config('site');
        $type = input('type');
        switch ($type) {
            case 'wall3':
                $bname = 'BTC';
                $hl = $config['btc2lmc'];
                break;
            case 'wall4':
                $bname = 'ETH';
                $hl = $config['eth2lmc'];
                break;
            case 'wall5':
                $bname = 'LTC';
                $hl = $config['ltc2lmc'];
                break;
        }

        $this->assign('bname', $bname);
        $this->assign('hl', $hl);
        $this->assign('type', $type);

        return $this->fetch();
    }

    public function myaccount()
    {
        $user = $this->auth->getUserinfo();
        //火币网 虚拟币行情 btcusdt/eosusdt/ethusdt/xrpusdt/bchusdt/htusdt/ltcusdt/etcusdt/ontusdt/iostusdt/
//        ["btcusdt"] => array(6) {
//        ["close"] => float(4049.59)
//        ["name"] => string(3) "btc"
//        ["open"] => float(3781.58)
//        ["rise_percent"] => float(0.0709)
//        ["symbol"] => string(7) "btcusdt"
//        ["weight"] => float(0.3408447161)
        $contents = json_decode(file_get_contents('https://www.huobi.co/-/x/general/index/constituent_symbol/detail'), true);
        $lists = $contents['data']['symbols'];
        foreach ($lists as $list) {
            $binfo[$list['symbol']] = $list;
        }
        $lmc = Db::name('kline')->order('id desc')->cache(true, 600)->find();
        $lmcrate = $lmc['close'];
        $btc_rate = $binfo['btcusdt']['close'];
        $ltc_rate = $binfo['ltcusdt']['close'];
        $eth_rate = $binfo['ethusdt']['close'];
        $eos_rate = $binfo['eosusdt']['close'];
        $total = ($user['wall1'] + $user['wall2'] + $user['freeze1'] + $user['freeze2']) * $lmcrate + ($user['wall3'] + $user['freeze3']) * $btc_rate + ($user['wall4'] + $user['freeze4']) * $eth_rate + ($user['wall5'] + $user['freeze5']) * $ltc_rate + ($user['wall6'] + $user['freeze6']) * $eos_rate;
        $this->assign('total', $total);
        $this->assign('btc_rate', $btc_rate);
        $this->assign('ltc_rate', $ltc_rate);
        $this->assign('eth_rate', $eth_rate);
        $this->assign('lmc_rate', $lmcrate);
        $this->assign('eos_rate', $eos_rate);

        return $this->fetch();
    }

    public function myaccount2()
    {
        $type = input('type');
        $this->assign('type', $type);

        return $this->fetch();
    }

    public function coinexchange2()
    {
        $type = input('type');
        $this->assign('type', $type);

        $contents = json_decode(file_get_contents('https://www.huobi.co/-/x/general/index/constituent_symbol/detail'), true);
        $lists = $contents['data']['symbols'];
        foreach ($lists as $list) {
            $binfo[$list['symbol']] = $list;
        }
        $lmc = Db::name('kline')->order('id desc')->cache(true, 600)->find();
        $lmc_rate = $lmc['close'];
        $btc_rate = $binfo['btcusdt']['close'];
        $ltc_rate = $binfo['ltcusdt']['close'];
        $eth_rate = $binfo['ethusdt']['close'];
        $eos_rate = $binfo['eosusdt']['close'];
        switch ($type) {
            case 'wall3':
                $bname = 'BTC';
                $hl = round($btc_rate / $lmc_rate, 4);
                break;
            case 'wall4':
                $bname = 'ETH';
                $hl = round($eth_rate / $lmc_rate, 4);
                break;
            case 'wall5':
                $bname = 'LTC';
                $hl = round($ltc_rate / $lmc_rate, 4);
                break;
            case 'wall6':
                $bname = 'EOS';
                $hl = round($eos_rate / $lmc_rate, 4);
                break;
        }
        $this->assign('hl', $hl);

        return $this->fetch();
    }

    /**
     * @return mixed
     *               实名认证页面
     */
    public function realname()
    {
        $userid = Session::get('uid');
        dump($userid);
        $user = db('user')->where('id', $userid)->find();
        $banks = db('banks')->select();
//        dump($user);exit;
//        if ($user['issm'] == 1){
//            $this->error('您已通过实名认证,请勿重复填写');
//        }
        $this->assign('banks', $banks);
        $this->assign('user', $user);

        return  $this->fetch();
    }

    //抽奖列表
    //抽奖列表
    public function luckydraw()
    {
        $user = $this->auth->getUserinfo();
        $luck = db('zhuan_prizelog')
            ->where('uid', $user['id'])
            ->select();
        if ($user['wall2'] < 3) {
            $num = 0;
        } else {
            $num = $user['lmccj'];
        }
        $this->assign('luck', $luck);
        $this->assign('num', $num);

        return $this->fetch();
    }

    //签到列表
    public function signin()
    {
        $user = $this->auth->getUser();

        $qian = Db::name('caiwu')->where(['userid' => $user['id'], 'type' => 16])->whereTime('addtime', 'today')->find();
        $p = $qian['price'] ? $qian['price'] : 0;

        $this->assign('today', $p);

        return $this->fetch();
    }

    /**
     * 我的收藏.
     */
    public function collection()
    {
        $user = $this->auth->getUserinfo();
        $list = array();
        if (!empty($user['collection'])) {
            $new = json_decode($user['collection'], true);
            $list = Db::name('shop_goods')->where(['goods_id' => ['in', $new]])->select();
        }
        $this->assign('list', $list);

        return $this->fetch();
    }

    public function successinfo()
    {
        $payback = input('payback');
        $this->assign('payback', $payback);

        return $this->fetch();
    }
}
