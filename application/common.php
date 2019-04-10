<?php

// 公共助手函数

use think\Db;
use tree\Tree2;

if (!function_exists('__')) {
    /**
     * 获取语言变量值
     *
     * @param string $name 语言变量名
     * @param array  $vars 动态变量值
     * @param string $lang 语言
     *
     * @return mixed
     */
    function __($name, $vars = [], $lang = '')
    {
        if (is_numeric($name) || !$name) {
            return $name;
        }
        if (!is_array($vars)) {
            $vars = func_get_args();
            array_shift($vars);
            $lang = '';
        }

        return \think\Lang::get($name, $vars, $lang);
    }
}

if (!function_exists('format_bytes')) {
    /**
     * 将字节转换为可读文本.
     *
     * @param int    $size      大小
     * @param string $delimiter 分隔符
     *
     * @return string
     */
    function format_bytes($size, $delimiter = '')
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
        for ($i = 0; $size >= 1024 && $i < 6; ++$i) {
            $size /= 1024;
        }

        return round($size, 2).$delimiter.$units[$i];
    }
}

if (!function_exists('datetime')) {
    /**
     * 将时间戳转换为日期时间.
     *
     * @param int    $time   时间戳
     * @param string $format 日期时间格式
     *
     * @return string
     */
    function datetime($time, $format = 'Y-m-d H:i:s')
    {
        $time = is_numeric($time) ? $time : strtotime($time);

        return date($format, $time);
    }
}

if (!function_exists('human_date')) {
    /**
     * 获取语义化时间.
     *
     * @param int $time  时间
     * @param int $local 本地时间
     *
     * @return string
     */
    function human_date($time, $local = null)
    {
        return \fast\Date::human($time, $local);
    }
}

if (!function_exists('cdnurl')) {
    /**
     * 获取上传资源的CDN的地址
     *
     * @param string $url    资源相对地址
     * @param bool   $domain 是否显示域名 或者直接传入域名
     *
     * @return string
     */
    function cdnurl($url, $domain = false)
    {
        $url = preg_match("/^https?:\/\/(.*)/i", $url) ? $url : \think\Config::get('upload.cdnurl').$url;
        if ($domain && !preg_match("/^(http:\/\/|https:\/\/)/i", $url)) {
            if (is_bool($domain)) {
                $public = \think\Config::get('view_replace_str.__PUBLIC__');
                $url = rtrim($public, '/').$url;
                if (!preg_match("/^(http:\/\/|https:\/\/)/i", $url)) {
                    $url = request()->domain().$url;
                }
            } else {
                $url = $domain.$url;
            }
        }

        return $url;
    }
}

if (!function_exists('is_really_writable')) {
    /**
     * 判断文件或文件夹是否可写.
     *
     * @param string $file 文件或目录
     *
     * @return bool
     */
    function is_really_writable($file)
    {
        if (DIRECTORY_SEPARATOR === '/') {
            return is_writable($file);
        }
        if (is_dir($file)) {
            $file = rtrim($file, '/').'/'.md5(mt_rand());
            if (($fp = @fopen($file, 'ab')) === false) {
                return false;
            }
            fclose($fp);
            @chmod($file, 0777);
            @unlink($file);

            return true;
        } elseif (!is_file($file) or ($fp = @fopen($file, 'ab')) === false) {
            return false;
        }
        fclose($fp);

        return true;
    }
}

if (!function_exists('rmdirs')) {
    /**
     * 删除文件夹.
     *
     * @param string $dirname  目录
     * @param bool   $withself 是否删除自身
     *
     * @return bool
     */
    function rmdirs($dirname, $withself = true)
    {
        if (!is_dir($dirname)) {
            return false;
        }
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dirname, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            $todo($fileinfo->getRealPath());
        }
        if ($withself) {
            @rmdir($dirname);
        }

        return true;
    }
}

if (!function_exists('copydirs')) {
    /**
     * 复制文件夹.
     *
     * @param string $source 源文件夹
     * @param string $dest   目标文件夹
     */
    function copydirs($source, $dest)
    {
        if (!is_dir($dest)) {
            mkdir($dest, 0755, true);
        }
        foreach (
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST) as $item
        ) {
            if ($item->isDir()) {
                $sontDir = $dest.DS.$iterator->getSubPathName();
                if (!is_dir($sontDir)) {
                    mkdir($sontDir, 0755, true);
                }
            } else {
                copy($item, $dest.DS.$iterator->getSubPathName());
            }
        }
    }
}

if (!function_exists('mb_ucfirst')) {
    function mb_ucfirst($string)
    {
        return mb_strtoupper(mb_substr($string, 0, 1)).mb_strtolower(mb_substr($string, 1));
    }
}

if (!function_exists('addtion')) {
    /**
     * 附加关联字段数据.
     *
     * @param array $items  数据列表
     * @param mixed $fields 渲染的来源字段
     *
     * @return array
     */
    function addtion($items, $fields)
    {
        if (!$items || !$fields) {
            return $items;
        }
        $fieldsArr = [];
        if (!is_array($fields)) {
            $arr = explode(',', $fields);
            foreach ($arr as $k => $v) {
                $fieldsArr[$v] = ['field' => $v];
            }
        } else {
            foreach ($fields as $k => $v) {
                if (is_array($v)) {
                    $v['field'] = isset($v['field']) ? $v['field'] : $k;
                } else {
                    $v = ['field' => $v];
                }
                $fieldsArr[$v['field']] = $v;
            }
        }
        foreach ($fieldsArr as $k => &$v) {
            $v = is_array($v) ? $v : ['field' => $v];
            $v['display'] = isset($v['display']) ? $v['display'] : str_replace(['_ids', '_id'], ['_names', '_name'], $v['field']);
            $v['primary'] = isset($v['primary']) ? $v['primary'] : '';
            $v['column'] = isset($v['column']) ? $v['column'] : 'name';
            $v['model'] = isset($v['model']) ? $v['model'] : '';
            $v['table'] = isset($v['table']) ? $v['table'] : '';
            $v['name'] = isset($v['name']) ? $v['name'] : str_replace(['_ids', '_id'], '', $v['field']);
        }
        unset($v);
        $ids = [];
        $fields = array_keys($fieldsArr);
        foreach ($items as $k => $v) {
            foreach ($fields as $m => $n) {
                if (isset($v[$n])) {
                    $ids[$n] = array_merge(isset($ids[$n]) && is_array($ids[$n]) ? $ids[$n] : [], explode(',', $v[$n]));
                }
            }
        }
        $result = [];
        foreach ($fieldsArr as $k => $v) {
            if ($v['model']) {
                $model = new $v['model']();
            } else {
                $model = $v['name'] ? Db::name($v['name']) : Db::table($v['table']);
            }
            $primary = $v['primary'] ? $v['primary'] : $model->getPk();
            $result[$v['field']] = $model->where($primary, 'in', $ids[$v['field']])->column("{$primary},{$v['column']}");
        }

        foreach ($items as $k => &$v) {
            foreach ($fields as $m => $n) {
                if (isset($v[$n])) {
                    $curr = array_flip(explode(',', $v[$n]));

                    $v[$fieldsArr[$n]['display']] = implode(',', array_intersect_key($result[$n], $curr));
                }
            }
        }

        return $items;
    }
}

if (!function_exists('var_export_short')) {
    /**
     * 返回打印数组结构.
     *
     * @param string $var    数组
     * @param string $indent 缩进字符
     *
     * @return string
     */
    function var_export_short($var, $indent = '')
    {
        switch (gettype($var)) {
            case 'string':
                return '"'.addcslashes($var, "\\\$\"\r\n\t\v\f").'"';
            case 'array':
                $indexed = array_keys($var) === range(0, count($var) - 1);
                $r = [];
                foreach ($var as $key => $value) {
                    $r[] = "$indent    "
                        .($indexed ? '' : var_export_short($key).' => ')
                        .var_export_short($value, "$indent    ");
                }

                return "[\n".implode(",\n", $r)."\n".$indent.']';
            case 'boolean':
                return $var ? 'TRUE' : 'FALSE';
            default:
                return var_export($var, true);
        }
    }
}
if (!function_exists('var_checkmoble')) {
    /**
     * 返回打印数组结构.
     *
     * @param string $var    数组
     * @param string $indent 缩进字符
     *
     * @return string
     */
    function var_checkmoble($mobile)
    {
//        $url = 'https://api.253.com/open/unn/ucheck';     //空号检测
//        $url = 'https://api.253.com/open/wool/wcheck';    //羊毛党检测
//        $params = [
//            'appId'  => 'xrhmO5r8', // appId,登录万数平台查看
//            'appKey' => 'Y0ZG1H1y', // appKey,登录万数平台查看
//            'mobile' => $mobile, // 要检测的手机号，限单个，仅支持11位国内号码
//        ];
//        $ch = curl_init();
//        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//        curl_setopt($ch, CURLOPT_HEADER, 0);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//        curl_setopt($ch, CURLOPT_URL, $url);
//        curl_setopt($ch, CURLOPT_POST, 1);
//        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
//        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
//        $result = curl_exec($ch);
//        $re = json_decode($result, true);
//        if ($re['code'] == 200000) {
//            return true;
//        } else {
//            return false;
//        }
        if (!$mobile || !\think\Validate::regex($mobile, "^1[3-9]\d{9}$")) {
            return false;
        } else {
            return true;
        }
    }
}
if (!function_exists('authcode')) {
    /**
     * @param string $string    要加密或解密的字符串
     * @param string $operation 加密或者解密
     * @param string $key       加解密秘钥
     * @param number $expiry    密码的位数
     *
     * @return string
     */
    function authcode($string, $operation = 'ENCODE', $key = '', $expiry = 0)
    {
        $ckey_length = 4;
        $key = md5($key ? $key : 'default_key');
        $keya = md5(substr($key, 0, 16));
        $keyb = md5(substr($key, 16, 16));
        $keyc = ($ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '');
        $cryptkey = $keya.md5($keya.$keyc);
        $key_length = strlen($cryptkey);
        $string = ($operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string);
        $string_length = strlen($string);
        $result = '';
        $box = range(0, 255);
        $rndkey = array();
        for ($i = 0; $i <= 255; ++$i) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }
        for ($j = $i = 0; $i < 256; ++$i) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }
        for ($a = $j = $i = 0; $i < $string_length; ++$i) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ $box[($box[$a] + $box[$j]) % 256]);
        }
        if ($operation == 'DECODE') {
            if (((substr($result, 0, 10) == 0) || (0 < (substr($result, 0, 10) - time()))) && (substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16))) {
                return substr($result, 26);
            } else {
                return '';
            }
        } else {
            return $keyc.str_replace('=', '', base64_encode($result));
        }
    }
}

/**
 * @param string $uid    用户id
 * @param number $price  金额
 * @param number $type   资金类型
 * @param string $ptype  钱包类型
 * @param string $memo   操作说明
 * @param int    $freeze 冻结 0=关 1=开
 *
 * @return bool
 */
function caiwu($uid, $price, $type, $ptype, $memo, $freeze = 0)
{
    //开启事务,避免出现垃圾数据
    Db::startTrans();
    try {
        $rshy = Db::name('user')->where(array('id' => $uid))->find();
        unset($caiwuarr);
        $caiwuarr['userid'] = $rshy['id'];
        $caiwuarr['account'] = $rshy['username'];
        $caiwuarr['yprice'] = $rshy[$ptype];
        $caiwuarr['nprice'] = $rshy[$ptype] + $price;
        $caiwuarr['price'] = $price;
        $caiwuarr['type'] = $type;
        $caiwuarr['ptype'] = $ptype;
        $caiwuarr['addtime'] = time();
        $caiwuarr['memo'] = $memo;
        if ($caiwuarr['nprice'] >= 0) {
            Db::name('user')->where(array('id' => $uid))->setField($ptype, $caiwuarr['nprice']);
            if ($freeze == 1) {
                $freezewall = ['wall1' => 'freeze1', 'wall2' => 'freeze2', 'wall3' => 'freeze3', 'wall4' => 'freeze4', 'wall5' => 'freeze5', 'wall6' => 'freeze6', 'wall7' => 'freeze7', 'v1' => 'freezev1'];
                $dongprice = $rshy[$freezewall[$ptype]] - $price;
                Db::name('user')->where(array('id' => $uid))->setField($freezewall[$ptype], $dongprice);
            }
            Db::name('caiwu')->insert($caiwuarr);
            Db::commit();

            return true;
        }
    } catch (\think\exception\DbException $e) {
        Db::rollback();

        return false;
    }
}

/**
 * 增加矿机.
 *
 * @param $uid
 * @param $gid
 *
 * @return bool
 *Create by xiaoniu
 */
function addMine($uid, $username, $gid, $iszs = 1)
{
    $goods = Db::name('goods')->where(array('goods_id' => $gid))->find();
    if (!$goods) {
        return false;
    }
    unset($array);
    $array['uid'] = $uid;
    $array['account'] = $username;
    $array['gid'] = $gid;
    $array['gname'] = $goods['goods_name'];
    $array['yxzq'] = $goods['yxzq'];
    $array['sybl'] = $goods['sybl']; //矿机收益比率
    $array['kjsl'] = $goods['kjsl'];
    $array['addtime'] = time(); //购买矿机的时间
    $array['order_sn'] = 'KJ'.mt_rand(1000, 9999).$uid; //矿机订单编号
    $array['iszs'] = $iszs; //0购买 1增送
    $order = Db::name('order')->insert($array);
    if ($order) {
        return true;
    } else {
        return false;
    }
}

/**
 * 矿机状态
 *Create by xiaoniu.
 */
function kjstate($status)
{
    switch ($status) {
        case 0:
            $sname = '未运行';
            break;
        case 1:
            $sname = '运行中';
            break;
        case 2:
            $sname = '已停止';
            break;
    }

    return $sname;
}

function reformat($arrTmp, &$ret = null)
{
    foreach ($arrTmp as $k => $v) {
        $ret[] = array('ceng' => $v['ceng'], 'ztnum' => count($arrTmp), 'user_id' => $v['id'], 'level' => $v['level'], 'jointime' => $v['jointime'], 'status' => $v['status'], 'tzprice' => $v['tzprice']);
        if ($v['children']) {
            reformat($v['children'], $ret);
        }
    }

    return $ret;
}

//实时更新级别，团队，直推
//state !=0 强制更新上级
function update_user_tui2($user_id, $state = 0)
{
    $users = Db::name('user');
    $categories = $users->select();
    $tree = new Tree2();
    $newCategories = [];
    foreach ($categories as $item) {
        $item['id'] = $item['id'];
        $item['name'] = '  用户名：'.$item['username'].'  用户id：'.$item['id'].'  团队人数:'.$item['tdnum'].'  直推人数:'.$item['ztnum'];
        array_push($newCategories, $item);
    }
    $tree->init($newCategories);
    $treeStr = $tree->getTreeArray4($user_id);
//      dump($treeStr);
    $re = reformat($treeStr);
//     dump($re);
    $ztnum = 0;     //现在直推

    if ($re) {
        foreach ($re as $value) {
            if ($value['ceng'] == 2) {
                $tt[$value['level']][] = $value;
            }
        }
    }

    $tdnum = 0;  //现在团队
    if ($re !== null) {
        foreach ($re as $tr) {
//            if ($tr['jointime'] > 0 && $tr['status'] == '1' && $tr['tzprice'] > 0) {
            if ($tr['jointime'] > 0 && $tr['status'] == '1') {
                ++$tdnum;
            }
            if ($tr['ceng'] == 2) {
//                if ($tr['jointime'] > 0 && $tr['status'] == '1' && $tr['tzprice'] > 0) {
                if ($tr['jointime'] > 0 && $tr['status'] == '1') {
                    ++$ztnum;
                }
            }
        }
    }

    //升级规则
    // $now  现在级别
    $info = $users->where(['id' => $user_id])->find();
    $levels = Db::name('user_level')->select();

    $now = $info['level'];
    if ($now == 1) {
        if ($info['ztnum'] >= 6 && $info['ztnum'] >= 35 && $info['tdsl'] >= $levels[1]['suan']) {
            $now = 2;
            //送矿机
            addMine($info['id'], $info['username'], $now, 1);
        }
    } elseif ($now < 5) {
        if (!empty($tt[$now])) {
            if (count($tt[$now]) > 2 && $info['tdsl'] >= $levels[$now]['suan']) {
                $now = $now + 1;
                //送矿机
                addMine($info['id'], $info['username'], $now, 1);
            }
        }
    } else {
        $now = 5;
    }

//    $levels = Db::name('user_level')->select();
//    foreach ($levels as $key => $level) {
//        if ($ztnum >= $level['ztnum'] && $tdnum >= $level['tdnum']) $now = $level['level_id'];
//    }
//
//    //测试显示
//      echo $user_id.'现在级别'.$now.'团队数量'.$tdnum.'直推'. $ztnum.'<br>';

    if ($info['tdnum'] != $tdnum || $info['ztnum'] != $ztnum || $info['level'] != $now || $state != 0) {
//         echo $info['account'].'现在级别'.$now.'团队数量'.$tdnum.'直推'. $ztnum.'<br>';

        $users->where('id='.$user_id)->setField(['tdnum' => $tdnum, 'ztnum' => $ztnum, 'level' => $now]);    //更新团队 直推 级别
        //更新父级
        $path = explode(',', $info['tpath']);
        unset($path[0]);
        if ($path) {
            foreach ($path as $value) {
                update_user_tui2($value);
            }
        }
    }

    return true;
}

function update_user_tui($user_id, $state = 0)
{
    $info = Db::name('user')->where(['id' => $user_id])->find();
    $map['jointime'] = ['>', 0];
//    $map['status'] = '1';
    $map['issm'] = '1';
    $downusers = Db::name('user')->where("find_in_set('{$user_id}',tpath)")->where($map)->field('id')->select();
    $ztusers = Db::name('user')->where('tjid', $user_id)->where($map)->field('id,level')->select();
    $ztnum = count($ztusers);  //现在直推
    $tdnum = count($downusers);  //伞下人数

    foreach ($ztusers as $value) {
        $tt[$value['level']][] = $value;
    }

    //升级规则
    $levelsong = ['等级送额度', 50, 50, 120, 200, 300];
    // $now  现在级别
    $levels = Db::name('user_level')->select();
    $now = $info['level'];
    if ($now == 1) {
        if ($ztnum >= 6 && $tdnum >= 35 && $info['tdsl'] >= $levels[1]['suan']) {
            $now = 2;
            $gid = $now + 1;
            //送矿机
            addMine($info['id'], $info['username'], $gid, 1);
            //送额度
            caiwu($info['id'], $levelsong[$now], 8, 'wall7', '升级奖励');
        }
    } elseif ($now < 5) {
        if (!empty($tt[$now])) {
            if (count($tt[$now]) > 2 && $info['tdsl'] >= $levels[$now]['suan']) {
                $now = $now + 1;
                $gid = $now + 1;
                //送矿机
                addMine($info['id'], $info['username'], $gid, 1);
                //送额度
                caiwu($info['id'], $levelsong[$now], 8, 'wall7', '升级奖励');
            }
        }
    } else {
        $now = 5;
    }
//
    ////    $levels = Db::name('user_level')->select();
    ////    foreach ($levels as $key => $level) {
    ////        if ($ztnum >= $level['ztnum'] && $tdnum >= $level['tdnum']) $now = $level['level_id'];
    ////    }
    ////
    ////    //测试显示
//      echo $user_id.'现在级别'.$now.'团队数量'.$tdnum.'直推'. $ztnum.'<br>';
//
    ////
    if ($info['tdnum'] != $tdnum || $info['ztnum'] != $ztnum || $info['level'] != $now || $state != 0) {
//    if ($info['tdnum'] != $tdnum || $info['ztnum'] != $ztnum || $state != 0) {
//        echo 'uid' . $user_id . '直推人数' . $ztnum . '团队人数' . $tdnum . '<br>';
//        Db::name('user')->where('id=' . $user_id)->setField(['tdnum' => $tdnum, 'ztnum' => $ztnum]);    //更新团队 直推 级别
        Db::name('user')->where('id='.$user_id)->setField(['tdnum' => $tdnum, 'ztnum' => $ztnum, 'level' => $now]);    //更新团队 直推 级别
        //更新父级
        $path = explode(',', $info['tpath']);
        unset($path[0]);
        if ($path) {
            foreach ($path as $value) {
                update_user_tui($value);
            }
        }
    }

    return true;
}

/**
 * @param $uid
 * @param int $money
 * @param int $type  1推荐奖 2对冲转账奖 3复投奖
 *
 * @return bool
 *
 * @throws \think\db\exception\DataNotFoundException
 * @throws \think\db\exception\ModelNotFoundException
 * @throws \think\exception\DbException
 */
function dtj($uid, $money = 0, $type = 1)
{
    ////    $myset = config('site.dtj');
//    $rules = Db::name('user_level')->select();
//    $user = Db::name('user');
//    $DM = $JM = $DD = [];
//    $zt = $dc = $fu = $td = [];
//    foreach ($rules as $rule) {
//        $DD[$rule['level_id']] = $rule['ceng'];     //拿奖代数
//        $zt[$rule['level_id']] = json_decode($rule['ztarr'], true);  //直推奖制度
//        $dc[$rule['level_id']] = json_decode($rule['tdarr'], true);  //对冲奖制度
//        $fu[$rule['level_id']] = json_decode($rule['fuarr'], true);  //复投奖制度
//        $td[$rule['level_id']] = $rule['tdj'];       //团队奖
//    }
//
//    $info = $user->where('id=' . $uid)->find();
//
//    $path = explode(",", $info['tpath']);
//    unset($path[0]);
//    if ($path) {
//        $mens = $user->where(['id' => ['in', $path]])->order('id desc')->select();  //分红人员
//
//        for ($i = 0; $i < count($mens); $i++) {
//            $ztj = $fuj = $dcj =$tdj= 0;
//            $k = $i + 1;  //代
//            if ($k == 1 && $type == 1) {
//                $ztrate = 0;   //直推奖比率
//                //vip推vip
//                if ($mens[$i]['level'] == 2 && $info['level'] == 2) {
//                    $ztrate = 2;
//                } else {
//                    foreach ($zt[$mens[$i]['level']] as $key => $item) {
//                        if ($key <= $mens[$i]['ztnum']) $ztrate = $item;
//                    }
//                }
//                $ztj = round($money * $ztrate * 0.01, 2);
//                if ($ztj > 0) caiwu($mens[$i]['id'], $ztj, 2, 'wall3', '直推奖');
//                if ($ztj > 0 && $mens[$i]['level']==2) caiwu($mens[$i]['id'], $ztj, 2, 'wall2', '直推奖');
//            }
//
//
//            if ($k <= $DD[$mens[$i]['level']]) {
//
//                if ($type == 2) {
//                    $dcrate = 0;   //对冲奖比率
//                    foreach ($dc[$mens[$i]['level']] as $key2 => $item2) {
//                        if ($key2 <= $mens[$i]['tdnum']) $dcrate = $item2;
//                    }
//                    $dcj = round($money * $dcrate * 0.01, 2);
//                    if ($dcj > 0) caiwu($mens[$i]['id'], $dcj, 2, 'wall3', $k . '代对冲奖');
//                    if ($dcj > 0 && $mens[$i]['level']==2) caiwu($mens[$i]['id'], $dcj, 2, 'wall2', $k . '代对冲奖');
//                    if ($mens[$i]['level']==2){
//                        $tdj= $money*$td[$mens[$i]['level']]*0.01;
//                        if($tdj>0){
//                            caiwu($mens[$i]['id'], $tdj, 2, 'wall3', $k . '代伞下业绩奖励');
//                            caiwu($mens[$i]['id'], $tdj, 2, 'wall2', $k . '代伞下业绩奖励');
//                        }
//                    }
//                }
//
//                if ($type == 3) {
//                    $furate = 0;   //复投奖比率
//                    foreach ($fu[$mens[$i]['level']] as $key3 => $item3) {
//                        if ($key3 <= $mens[$i]['tdnum']) $furate = $item3;
//                    }
//                    $fuj = round($money * $furate * 0.01, 2);
//                    if ($fuj > 0) caiwu($mens[$i]['id'], $fuj, 2, 'wall3', $k . '代复投奖');
//                    if ($fuj > 0 && $mens[$i]['level']==2)  caiwu($mens[$i]['id'], $fuj, 2, 'wall2', $k . '代复投奖');
//                    if ($mens[$i]['level']==2){
//                        $tdj= $money*$td[$mens[$i]['level']]*0.01;
//                        if($tdj>0){
//                            caiwu($mens[$i]['id'], $tdj, 2, 'wall3', $k . '代伞下业绩奖励');
//                            caiwu($mens[$i]['id'], $tdj, 2, 'wall2', $k . '代伞下业绩奖励');
//                        }
//                    }
//                }
//
//            }
//        }
//    }
//    return true;
}

/**
 * 级差奖.
 */
function jcj($uid, $money = 0)
{
//    $myset = config('site');
//    $rules = Db::name('user_level')->select();
//    $user = Db::name('user');
//    $DM = $JM = $DD = [];
//    foreach ($rules as $rule) {
//        $DD[$rule['level_id']] = $rule['tdj'];
//        $DM[$rule['level_id']] = $rule['level_name'];
//    }
//    $info = $user->where('id=' . $uid)->find();
//    $path = explode(",", $info['tpath']);
//    unset($path[0]);
//    $shao = $money * $myset['jcrate'] * 0.01;
//    if ($path) {
//        $mens = $user->where(['id' => ['in', $path]])->order('id desc')->select();  //分红人员
//
//        $kk = $last = 0; //分红统计
//        for ($i = 0; $i < count($mens); $i++) {
//            $k = $i + 1;  //代
//            if ($i == 0) {
//                if ($DD[$mens[$i]['level']] > 0) {
//                    $fen[$i] = $shao * $DD[$mens[$i]['level']] * 0.01;
//                    $isfa = $fen[$i];
//                    $last = $fen[$i];
//
//                    $kk += $DD[$mens[$i]['level']];
//
//                } else {
//                    $isfa = 0;
//                }
////                dump('uid' . $mens[$i]['id'] . 'level' . $mens[$i]['level'].'levelrate' . $DD[$mens[$i]['level']].$isfa);
//
//            } else {
//                if ($mens[$i]['level'] - $mens[$i - 1]['level'] > 0 && $DD[$mens[$i]['level']] - $kk > 0) {
//                    $fen[$i] = $shao * ($DD[$mens[$i]['level']] - $kk) * 0.01;
//                    $isfa = $fen[$i];
//                    $last = $fen[$i];
//                    $kk += ($DD[$mens[$i]['level']] - $kk);
//                } elseif ($mens[$i]['level'] - $mens[$i - 1]['level'] == 0) {
//                    $isfa = $last * $myset['samelevel'] * 0.01;
//                } else {
//                    $isfa = 0;
//                }
////                dump('Suid' . $mens[$i]['id'] . 'Slevel' . $mens[$i]['level'].'Slevelrate' . $DD[$mens[$i]['level']].$isfa);
//            }
//            if ($isfa > 0 && $mens[$i]['jointime'] > 0 && $mens[$i]['status'] == '1') {
////                dump('Suid' . $mens[$i]['id'] . 'Slevel' . $mens[$i]['level'].'Slevelrate' . $DD[$mens[$i]['level']].$isfa);
//                caiwu($mens[$i]['id'], $isfa, 8, 'wall6', $info['username'] . '级差分红奖-' . $DM[$mens[$i]['level']]);
//                caiwu($mens[$i]['id'], $isfa, 8, 'wall2', $info['username'] . '级差分红奖额度-' . $DM[$mens[$i]['level']]);
//            }
//        }
//    }
//    return true;
}

//虚拟币冻结
function dongjie($uid, $account, $info = '系统冻结', $state = 0)
{
    $row = Db::name('user')->where(array('id' => $uid))->setField('status', $state);
    if ($state == 1) {
        Db::name('xymx')->where(['userid' => $uid, 'status' => 4])->setField('status', 0); //解冻
        Db::name('tgmx')->where(['userid' => $uid, 'status' => 4])->setField('status', 0); //解冻
    }
    if ($state == 0) {
        Db::name('xymx')->where(['userid' => $uid, 'status' => 0])->setField('status', 4); //冻结
        Db::name('tgmx')->where(['userid' => $uid, 'status' => 0])->setField('status', 4); //冻结

        //冻结日志
        Db::name('dong')->insert(['uid' => $uid, 'info' => $info, 'username' => $account, 'addtime' => time()]);
    }
    if ($row) {
        update_user_tui($uid, 1);

        return true;
    } else {
        return false;
    }
}

//互助冻结
function dongjie2($uid, $account, $info = '系统冻结', $state = 0)
{
    $row = Db::name('user')->where(array('id' => $uid))->setField('status', $state);
    if ($state == 1) {
        Db::name('xymx2')->where(['userid' => $uid, 'status' => 4])->setField('status', 0); //解冻
        Db::name('tgmx2')->where(['userid' => $uid, 'status' => 4])->setField('status', 0); //解冻
    }
    if ($state == 0) {
        Db::name('xymx2')->where(['userid' => $uid, 'status' => 0])->setField('status', 4); //冻结
        Db::name('tgmx2')->where(['userid' => $uid, 'status' => 0])->setField('status', 4); //冻结

        //冻结日志
        Db::name('dong')->insert(['uid' => $uid, 'info' => $info, 'username' => $account, 'addtime' => time()]);
    }
    if ($row) {
        update_user_tui($uid, 1);

        return true;
    } else {
        return false;
    }
}

//虚拟币匹配取消
function ppcancle($ppid, $state = 3)
{
    $rspp = Db::name('ppmx')->where(array('ppid' => $ppid))->find();
//    Db::name('tgmx')->where(array('tgid' => $rspp['tgid']))->setDec('buy_number', $rspp['number']);
//    Db::name('xymx')->where(array('xyid' => $rspp['xyid']))->setDec('sale_number', $rspp['number']);
//
//    Db::name('tgmx')->where(array('tgid' => $rspp['tgid']))->setField('status', 0);
//    Db::name('xymx')->where(array('xyid' => $rspp['xyid']))->setField('status', 0);

    $saleuid = $rspp['userid1'];
    $salefee = config('site.salefee');
    $fan = $rspp['number'] * $salefee * 0.01 + $rspp['number'];
    caiwu($saleuid, $fan, 6, 'wall2', '交易取消退回', 1);
    //授信额度
    caiwu($saleuid, $rspp['number'], 3, 'wall7', '交易取消退回', 1);
    //令牌额度
    $v1 = ceil($rspp['number'] / 50);
    caiwu($saleuid, $v1, 3, 'v1', '交易取消退回', 1);
    $re = Db::name('ppmx')->where(array('ppid' => $ppid))->setField('status', $state);
    if ($re) {
        return true;
    } else {
        return false;
    }
}

//互助匹配取消
function ppcancle2($ppid, $state = 3)
{
    $rspp = Db::name('ppmx2')->where(array('ppid' => $ppid))->find();
    Db::name('tgmx2')->where(array('tgid' => $rspp['tgid']))->setDec('buy_number', $rspp['number']);
    Db::name('xymx2')->where(array('xyid' => $rspp['xyid']))->setDec('sale_number', $rspp['number']);

    Db::name('tgmx2')->where(array('tgid' => $rspp['tgid']))->setField('status', 0);
    Db::name('xymx2')->where(array('xyid' => $rspp['xyid']))->setField('status', 0);
    $re = Db::name('ppmx2')->where(array('ppid' => $ppid))->setField('status', $state);
    if ($re) {
        return true;
    } else {
        return false;
    }
}

/**
 * 人脸识别.
 */
function face($idCardImage, $photo)
{
    $appId = 'xrhmO5r8';
    $appKey = 'Y0ZG1H1y';
    $url = 'https://api.253.com/open/i/witness/witness-check';
    $params = [
        'appId' => $appId, // appId,登录万数平台查看
        'appKey' => $appKey, // appKey,登录万数平台查看
        'liveImage' => $photo, // 活体检测的自拍照片。imageType为URL时，传入照片的网络URL地址, 支持jpg/png/bmp格式，imageType为BASE64时，传入照片的base64字符编码，base64字符串不包含data:image前缀，且图片大小不能大于2M
        'idCardImage' => $idCardImage, // 身份证照片，请确保身份证内容信息清晰可见，imageType为URL时，传入照片的网络URL地址，imageType为BASE64时，传入照片的base64字符编码，base64字符串不包含data:image前缀，且图片大小不能大于2M
        'imageType' => 'URL', // 图片类型，枚举值：URL-图片路径；BASE64 –图片BASE64编码
    ];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $result = curl_exec($ch);

    return  json_decode($result, true);
    exit;
}

/**
 * 银行卡四要素.
 */
function bankcard($username, $idnum, $cardno, $mobile)
{
    $appId = 'xrhmO5r8';
    $appKey = 'Y0ZG1H1y';
    $url = 'https://api.253.com/open/bankcard/card-auth-detail';
    $params = [
        'appId' => $appId, // appId,登录万数平台查看
        'appKey' => $appKey, // appKey,登录万数平台查看
        'name' => $username, // 姓名
        'idNum' => $idnum, // 身份证号码，限单个
        'cardNo' => $cardno, // 银行卡号，限单个
        'mobile' => $mobile, // 银行预留手机号码，限单个，仅支持国内11位号码
    ];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $result = curl_exec($ch);

    return  json_decode($result, true);
    exit;
}

/**
 * 获取随机字符串.
 *
 * @param int $randLength    长度
 * @param int $addtime       是否加入当前时间戳
 * @param int $includenumber 是否包含数字
 *
 * @return string
 */
function get_rand_str($randLength = 6, $addtime = 1, $includenumber = 0)
{
    if ($includenumber) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHJKLMNPQEST123456789';
    } else {
        $chars = 'abcdefghijklmnopqrstuvwxyz';
    }
    $len = strlen($chars);
    $randStr = '';
    for ($i = 0; $i < $randLength; ++$i) {
        $randStr .= $chars[rand(0, $len - 1)];
    }
    $tokenvalue = $randStr;
    if ($addtime) {
        $tokenvalue = $randStr.time();
    }

    return $tokenvalue;
}
function team($uid, $num)
{
    $categories = db('user')->cache(true, 600)->select();

//        $allinfo = array_reverse(explode(',',$categories['tpath']),false);
//        var_dump($allinfo);exit;
    //var_dump($categories);exit;
    $level = [];
    foreach ($categories as $vo) {
        $allinfo = array_reverse(explode(',', $vo['tpath']), false);
        if (isset($allinfo[$num])) {
            if ($allinfo[$num] == $uid) {
                array_push($level, $vo);
            }
        }
    }

    return $level;
}
function getkj($uid)
{
    $kjnum = db('order')->cache(true, 600)->where(array('uid' => $uid, 'status' => '1'))->count();

    return $kjnum;
}

/**
 * 消费lmc计算 返回数组[需要LMC，LMC当前价/$].
 *
 * @param $cny
 *
 * @return array
 *
 * @throws \think\db\exception\DataNotFoundException
 * @throws \think\db\exception\ModelNotFoundException
 * @throws \think\exception\DbException
 */
function need_lmc($cny)
{
    $usd2cny = config('site.usd2cny');
    $lmc = Db::name('kline')->order('id desc')->cache(true, 600)->find();
    $lmcrate = $lmc['close'] > 0 ? $lmc['close'] : 1;
    $need = $cny / $usd2cny / $lmcrate;
    $re = [round($need, 4), $lmcrate];

    return $re;
}
function lmc($cny)
{
    $usd2cny = config('site.usd2cny');
    $lmc = Db::name('kline')->order('id desc')->cache(true, 600)->find();
    $lmcrate = $lmc['close'] > 0 ? $lmc['close'] : 1;
    $need = $cny / $usd2cny / $lmcrate;
    $re = round($need, 4);

    return $re;
}
/**
 * 聚合请求接口返回内容.
 *
 * @param string $url    [请求的URL地址]
 * @param string $params [请求的参数]
 * @param int    $ipost  [是否采用POST形式]
 *
 * @return string
 */
function juhecurl($url, $params = false, $ispost = 0)
{
    $httpInfo = array();
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.22 (KHTML, like Gecko) Chrome/25.0.1364.172 Safari/537.22');
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if ($ispost) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_URL, $url);
    } else {
        if ($params) {
            curl_setopt($ch, CURLOPT_URL, $url.'?'.$params);
        } else {
            curl_setopt($ch, CURLOPT_URL, $url);
        }
    }
    $response = curl_exec($ch);
    if ($response === false) {
        //echo "cURL Error: " . curl_error($ch);
        return false;
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $httpInfo = array_merge($httpInfo, curl_getinfo($ch));
    curl_close($ch);

    return $response;
}
