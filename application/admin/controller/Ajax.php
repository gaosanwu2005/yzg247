<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use fast\Random;
use think\addons\Service;
use think\Cache;
use think\Config;
use think\Db;
use think\Lang;

/**
 * Ajax异步请求接口.
 *
 * @internal
 */
class Ajax extends Backend
{
    protected $noNeedLogin = ['lang'];
    protected $noNeedRight = ['*'];
    protected $layout = '';

    public function _initialize()
    {
        parent::_initialize();

        //设置过滤方法
        $this->request->filter(['strip_tags', 'htmlspecialchars']);
    }

    /**
     * 加载语言包.
     */
    public function lang()
    {
        header('Content-Type: application/javascript');
        $controllername = input('controllername');
        //默认只加载了控制器对应的语言名，你还根据控制器名来加载额外的语言包
        $this->loadlang($controllername);

        return jsonp(Lang::get(), 200, [], ['json_encode_param' => JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE]);
    }

    /**
     * 上传文件.
     */
    public function upload()
    {
        Config::set('default_return_type', 'json');
        $file = $this->request->file('file');
        if (empty($file)) {
            $this->error(__('No file upload or server upload limit exceeded'));
        }

        //判断是否已经存在附件
        $sha1 = $file->hash();

        $upload = Config::get('upload');

        preg_match('/(\d+)(\w+)/', $upload['maxsize'], $matches);
        $type = strtolower($matches[2]);
        $typeDict = ['b' => 0, 'k' => 1, 'kb' => 1, 'm' => 2, 'mb' => 2, 'gb' => 3, 'g' => 3];
        $size = (int) $upload['maxsize'] * pow(1024, isset($typeDict[$type]) ? $typeDict[$type] : 0);
        $fileInfo = $file->getInfo();
        $suffix = strtolower(pathinfo($fileInfo['name'], PATHINFO_EXTENSION));
        $suffix = $suffix ? $suffix : 'file';

        $mimetypeArr = explode(',', strtolower($upload['mimetype']));
        $mimetypeArr = array_merge($mimetypeArr, ['mp4', 'mp3', 'avi', 'flv', 'wmv']);
        $typeArr = explode('/', $fileInfo['type']);

        //验证文件后缀
        if ($upload['mimetype'] !== '*' &&
            (
                !in_array($suffix, $mimetypeArr)
                || (stripos($typeArr[0].'/', $upload['mimetype']) !== false && (!in_array($fileInfo['type'], $mimetypeArr) && !in_array($typeArr[0].'/*', $mimetypeArr)))
            )
        ) {
            $this->error(__('Uploaded file format is limited'));
        }
        $replaceArr = [
            '{year}' => date('Y'),
            '{mon}' => date('m'),
            '{day}' => date('d'),
            '{hour}' => date('H'),
            '{min}' => date('i'),
            '{sec}' => date('s'),
            '{random}' => Random::alnum(16),
            '{random32}' => Random::alnum(32),
            '{filename}' => $suffix ? substr($fileInfo['name'], 0, strripos($fileInfo['name'], '.')) : $fileInfo['name'],
            '{suffix}' => $suffix,
            '{.suffix}' => $suffix ? '.'.$suffix : '',
            '{filemd5}' => md5_file($fileInfo['tmp_name']),
        ];
        $savekey = $upload['savekey'];
        $savekey = str_replace(array_keys($replaceArr), array_values($replaceArr), $savekey);

        $uploadDir = substr($savekey, 0, strripos($savekey, '/') + 1);
        $fileName = substr($savekey, strripos($savekey, '/') + 1);

        $splInfo = $file->validate(['size' => $size])->move(ROOT_PATH.'/public'.$uploadDir, $fileName);
        if ($splInfo) {
            $imagewidth = $imageheight = 0;
            if (in_array($suffix, ['gif', 'jpg', 'jpeg', 'bmp', 'png'])) {
                $imgInfo = getimagesize($splInfo->getPathname());
                $imagewidth = isset($imgInfo[0]) ? $imgInfo[0] : $imagewidth;
                $imageheight = isset($imgInfo[1]) ? $imgInfo[1] : $imageheight;
                //图像压缩
                $all = $uploadDir.$splInfo->getSaveName();
                $image = new \think\Image();
                $image->open(ROOT_PATH.'/public'.$all);
                $image->thumb(1200, 1200, 1)->save(ROOT_PATH.'/public'.$all);
            }
            $params = array(
                'admin_id' => (int) $this->auth->id,
                'user_id' => 0,
                'filesize' => $fileInfo['size'],
                'imagewidth' => $imagewidth,
                'imageheight' => $imageheight,
                'imagetype' => $suffix,
                'imageframes' => 0,
                'mimetype' => $fileInfo['type'],
                'url' => $uploadDir.$splInfo->getSaveName(),
                'uploadtime' => time(),
                'storage' => 'local',
                'sha1' => $sha1,
            );
            $attachment = model('attachment');
            $attachment->data(array_filter($params));
            $attachment->save();
            \think\Hook::listen('upload_after', $attachment);
            $this->success(__('Upload successful'), null, [
                'url' => $uploadDir.$splInfo->getSaveName(),
            ]);
        } else {
            // 上传失败获取错误信息
            $this->error($file->getError());
        }
    }

    /**
     * 通用排序.
     */
    public function weigh()
    {
        //排序的数组
        $ids = $this->request->post('ids');
        //拖动的记录ID
        $changeid = $this->request->post('changeid');
        //操作字段
        $field = $this->request->post('field');
        //操作的数据表
        $table = $this->request->post('table');
        //排序的方式
        $orderway = $this->request->post('orderway', 'strtolower');
        $orderway = $orderway == 'asc' ? 'ASC' : 'DESC';
        $sour = $weighdata = [];
        $ids = explode(',', $ids);
        $prikey = 'id';
        $pid = $this->request->post('pid');
        //限制更新的字段
        $field = in_array($field, ['weigh']) ? $field : 'weigh';

        // 如果设定了pid的值,此时只匹配满足条件的ID,其它忽略
        if ($pid !== '') {
            $hasids = [];
            $list = Db::name($table)->where($prikey, 'in', $ids)->where('pid', 'in', $pid)->field('id,pid')->select();
            foreach ($list as $k => $v) {
                $hasids[] = $v['id'];
            }
            $ids = array_values(array_intersect($ids, $hasids));
        }

        //直接修复排序
        $one = Db::name($table)->field("{$field},COUNT(*) AS nums")->group($field)->having('nums > 1')->find();
        if ($one) {
            $list = Db::name($table)->field("$prikey,$field")->order($field, $orderway)->select();
            foreach ($list as $k => $v) {
                Db::name($table)->where($prikey, $v[$prikey])->update([$field => $k + 1]);
            }
            $this->success();
        } else {
            $list = Db::name($table)->field("$prikey,$field")->where($prikey, 'in', $ids)->order($field, $orderway)->select();
            foreach ($list as $k => $v) {
                $sour[] = $v[$prikey];
                $weighdata[$v[$prikey]] = $v[$field];
            }
            $position = array_search($changeid, $ids);
            $desc_id = $sour[$position];    //移动到目标的ID值,取出所处改变前位置的值
            $sour_id = $changeid;
            $desc_value = $weighdata[$desc_id];
            $sour_value = $weighdata[$sour_id];
            //echo "移动的ID:{$sour_id}\n";
            //echo "替换的ID:{$desc_id}\n";
            $weighids = array();
            $temp = array_values(array_diff_assoc($ids, $sour));
            foreach ($temp as $m => $n) {
                if ($n == $sour_id) {
                    $offset = $desc_id;
                } else {
                    if ($sour_id == $temp[0]) {
                        $offset = isset($temp[$m + 1]) ? $temp[$m + 1] : $sour_id;
                    } else {
                        $offset = isset($temp[$m - 1]) ? $temp[$m - 1] : $sour_id;
                    }
                }
                $weighids[$n] = $weighdata[$offset];
                Db::name($table)->where($prikey, $n)->update([$field => $weighdata[$offset]]);
            }
            $this->success();
        }
    }

    /**
     * 清空系统缓存.
     */
    public function wipecache()
    {
        $type = $this->request->request('type');
        switch ($type) {
            case 'content' || 'all':
                rmdirs(CACHE_PATH, false);
                Cache::clear();
                if ($type == 'content') {
                    break;
                }
                    // no break
            case 'template' || 'all':
                rmdirs(TEMP_PATH, false);
                if ($type == 'template') {
                    break;
                }
                    // no break
            case 'addons' || 'all':
                Service::refresh();
                if ($type == 'addons') {
                    break;
                }
        }

        \think\Hook::listen('wipecache_after');
        $this->success();
    }

    /**
     * 读取分类数据,联动列表.
     */
    public function category()
    {
        $type = $this->request->get('type');
        $pid = $this->request->get('pid');
        $where = ['status' => 'normal'];
        $categorylist = null;
        if ($pid !== '') {
            if ($type) {
                $where['type'] = $type;
            }
            if ($pid) {
                $where['pid'] = $pid;
            }

            $categorylist = Db::name('category')->where($where)->field('id as value,name')->order('weigh desc,id desc')->select();
        }
        $this->success('', null, $categorylist);
    }

    /**
     * 读取省市区数据,联动列表.
     */
    public function area()
    {
        $province = $this->request->get('row.province');
        $city = $this->request->get('row.city');
        $where = ['pid' => 0, 'level' => 1];
        $provincelist = null;
        if ($province !== '') {
            if ($province) {
                $where['pid'] = $province;
                $where['level'] = 2;
            }
            if ($city !== '') {
                if ($city) {
                    $where['pid'] = $city;
                    $where['level'] = 3;
                }
                $provincelist = Db::name('area')->where($where)->field('id as value,name')->select();
            }
        }
        $this->success('', null, $provincelist);
    }

    /**
     *  公共用户切换.
     */
    public function userchange($ids)
    {
        $p = input('params');
        $act = input('action');
        $re = Db::table('fa_user')->where('id', $ids)->setField($act, $p);
        if ($re) {
            $this->success('操作成功');
        } else {
            $this->error('操作失败');
        }
    }

    /**
     *  商品切换.
     */
    public function change($ids)
    {
        $p = input('params');
        $act = input('action');
        $re = Db::table('fa_shop_goods')->where('goods_id', $ids)->setField($act, $p);

        if ($re) {
            $this->success('操作成功');
        } else {
            $this->error('操作失败');
        }
    }

    /**
     *  用户冻结.
     */
    public function change2($ids)
    {
        $p = input('params');
//        $act=input('action');
//        Db::table('fa_user')->where('id',$ids)->setField($act,$p);
        $re = dongjie2($ids, $ids, $info = '后台操作', $state = $p);
        if ($re) {
            $this->success('操作成功');
        } else {
            $this->error('操作失败');
        }
    }

    /**
     *  代理审核.
     */
    public function change3($ids)
    {
        $p = input('params');
        $act = input('action');
        $info = Db::table('fa_user')->where('id', $ids)->find();
        if ($info['is_agent'] == 0) {
            $re = Db::table('fa_user')->where('id', $ids)->setField($act, $p);
            if ($re) {
                //开店审核逻辑
//                $info = Db::table('fa_user')->where('id', $ids)->find();
//                $admin_id = Db::table('fa_admin')->insertGetId([
//                    'username'   => $info['username'],
//                    'nickname'   => $info['username'],
//                    'password'   => $info['password'],
//                    'avatar'     => $info['avatar'],
//                    'createtime' => time(),
//                ]);
//                //后台增加商户
//                Db::table('fa_auth_group_access')->insert(['uid' => $admin_id, 'group_id' => 6]);  //后台增加权限
//
//                Db::table('fa_store')->insert(['admin_id' => $admin_id,'user_name'=>$info['username'],'store_avatar'=> $info['avatar'],]);  //后台增加店铺

                $this->success('操作成功');
            } else {
                $this->error('操作失败');
            }
        } else {
            $this->error('已经审核，请勿重复审核');
        }
    }

    /**
     *  开店审核.
     */
    public function change4($ids)
    {
        $p = input('params');
        $act = input('action');
        $info = Db::table('fa_user')->where('id', $ids)->find();
        if ($info['shop_open'] == 2) {
            $re = Db::table('fa_user')->where('id', $ids)->setField($act, $p);
            if ($re) {
                $this->success('操作成功');
            } else {
                $this->error('操作失败');
            }
        } else {
            $this->error('已经审核，请勿重复审核');
        }
    }

    /**
     *  实体审核.
     */
    public function change5($ids)
    {
        $p = input('params');
        $act = input('action');
        $info = Db::table('fa_user')->where('id', $ids)->find();
        if ($info['is_real'] == 2) {
            $re = Db::table('fa_user')->where('id', $ids)->setField($act, $p);
            if ($re) {
                $this->success('操作成功');
            } else {
                $this->error('操作失败');
            }
        } else {
            $this->error('已经审核，请勿重复审核');
        }
    }

    /**
     * 充币审核.
     *
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function eth2lmc()
    {
        $id = input('ids');
        $state = input('params');
        $info = Db::table('fa_tx_eth2lmc')->where('id', $id)->find();
        $data['comtime'] = time();
        switch ($state) {
            case 1:
                $data['status'] = $state;
                Db::table('fa_tx_eth2lmc')->where('id', $id)->update($data);
                caiwu($info['user_id'], $info['amount'], 1, $info['type'], '通过审核充值'.$info['paytype'].$info['amount']);
                break;
            case 2:
                $data['status'] = $state;
                Db::table('fa_tx_eth2lmc')->where('id', $id)->update($data);
        }
        $this->success('操作成功');
    }

    /**
     * 提币审核.
     *
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function lmc2eth()
    {
        $id = input('ids');
        $state = input('params');
        $info = Db::table('fa_tx_lmc2eth')->where('id', $id)->find();
        $data['comtime'] = time();
        $all = $info['amount'] + $info['service'];
        switch ($state) {
            case 1:
                $data['status'] = $state;
                Db::table('fa_tx_lmc2eth')->where('id', $id)->update($data);
                //减少冻结
                $freezewall = ['wall1' => 'freeze1', 'wall2' => 'freeze2', 'wall3' => 'freeze3', 'wall4' => 'freeze4', 'wall5' => 'freeze5', 'wall6' => 'freeze6'];
                Db::name('user')->where('id', $info['user_id'])->setDec($freezewall[$info['type']], $all);
                break;
            case 2:
                $data['status'] = $state;
                Db::table('fa_tx_lmc2eth')->where('id', $id)->update($data);
                caiwu($info['user_id'], $all, 13, $info['type'], '审核失败返还', 1);
        }

        $this->success('操作成功');
    }

    /***
     * 手机 流量 充值审核
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function pcharge()
    {
        $id = input('ids');
        $state = input('params');
        $info = Db::name('recharge_third')->where('order_id', $id)->find();

        switch ($state) {
            case 1:
                $data['status'] = $state;
                $re1 = Db::name('recharge_third')->where('order_id', $id)->update($data);
                $re2 = Db::name('user')->where('id', $info['user_id'])->setDec('freeze2', $info['num']);
                Db::name('user')->where('id', $info['user_id'])->setDec('freeze7', $info['num']);
                if ($re1 && $re2) {
                    $this->success('操作成功');
                }
                break;
            case 2:
                $data['status'] = $state;
                Db::name('recharge_third')->where('order_id', $id)->update($data);
                caiwu($info['user_id'], $info['num'], 1, 'wall2', '充值未通过审核退回', 1);
                caiwu($info['user_id'], $info['num'], 1, 'wall7', '充值未通过审核退回', 1);
                $this->success('操作成功');
                break;
        }
    }
}
