<?php

namespace addons\school\controller;

use app\common\controller\Api;
use think\Config;
use think\Db;
use fast\Random;

class Ajax extends Api
{
    protected $noNeedLogin = ['init'];
    protected $noNeedRight = '*';

    public function _initialize()
    {
        parent::_initialize();
    }

    public function getuser()
    {
        $user = $this->auth->getUserinfo();

        return json($user);
    }

    /**
     * 报名信息提交.
     */
    public function sendinfo()
    {
        $user = $this->auth->getUserinfo();
        $post = input('post.');
        $urls = $this->upload2();
        $post = array_merge($post, $urls);
        $post['uid'] = $user['id'];
        $info = Db::name('school_resume')->find($user['id']);
        if ($info) {
            $re = Db::name('school_resume')->strict(false)->update($post);
        } else {
            $re = Db::name('school_resume')->strict(false)->insert($post);
        }
        if ($re) {
            $this->success('操作成功', '', addon_url('school/index/xuelitisheng2'));
        } else {
            $this->error('操作失败', 789);
        }
    }

    /**
     * 报名信息提交.
     */
    public function sendinfo2()
    {
        $user = $this->auth->getUserinfo();
        $post = input('post.');
        $post['uid'] = $user['id'];
        $info = Db::name('school_resume')->find($user['id']);
        if ($info) {
            $re = Db::name('school_resume')->strict(false)->update($post);
        } else {
            $re = Db::name('school_resume')->strict(false)->insert($post);
        }
        if ($re) {
            $this->success('操作成功', '', url('index/index/index2'));
        } else {
            $this->error('操作失败');
        }
    }

    /**
     * 上传文件.
     *
     * @param File $file 文件流
     */
    public function upload2()
    {
        $files = $this->request->file();
        if (empty($files)) {
            return [];
        }
        foreach ($files as $key => $file) {
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
            $typeArr = explode('/', $fileInfo['type']);

            //验证文件后缀
            if ($upload['mimetype'] !== '*' && (!in_array($suffix, $mimetypeArr)
                || (stripos($typeArr[0].'/', $upload['mimetype']) !== false && (!in_array($fileInfo['type'], $mimetypeArr) && !in_array($typeArr[0].'/*', $mimetypeArr))))) {
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
            $fileName = md5(microtime());

            $splInfo = $file->validate(['size' => $size])->move(ROOT_PATH.'/public'.$uploadDir, $fileName);

            if ($splInfo) {
                $imagewidth = $imageheight = 0;
                if (in_array($suffix, ['gif', 'jpg', 'jpeg', 'bmp', 'png', 'swf'])) {
                    $imgInfo = getimagesize($splInfo->getPathname());
                    $imagewidth = isset($imgInfo[0]) ? $imgInfo[0] : $imagewidth;
                    $imageheight = isset($imgInfo[1]) ? $imgInfo[1] : $imageheight;
                }
                $params = array(
                    'admin_id' => 0,
                    'user_id' => (int) $this->auth->id,
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
                $attachment = db('attachment');
                $attachment->data(array_filter($params));
                $attachment->insert();
                \think\Hook::listen('upload_after', $attachment);
                //图像压缩
                $all = $uploadDir.$splInfo->getSaveName();
                $image = new \think\Image();
                $image->open(ROOT_PATH.'/public'.$all);
                $image->thumb(800, 800, 1)->save(ROOT_PATH.'/public'.$all);
                $url = $uploadDir.$splInfo->getSaveName();
                $tmp[$key] = $url;
            } else {
                // 上传失败获取错误信息
                return false;
            }
        }

        return $tmp;
    }

    /**
     * 黄金分时.
     */
    public function getau()
    {
        $date = date('YmdH', time() + 96400);
        $re = file_get_contents('http://webforex.hermes.hexun.com/forex/kline?code=FOREXXAUUSD&start='.$date.'0000&number=-960&type=0');
        $re = trim($re, '(');
        $re = trim($re, ');');
        $re = json_decode($re, true);
        foreach ($re['Data'][0] as $item) {
            $data[] = [
                $item[0] * 0.01,
                (float) $item[2],
                round($item[1] * 0.0001, 4),
                (float) $item[2],
                (float) $item[2],
            ];
        }
        $end = end($data);
        $re1 = [
            'date' => $date,
            'nowprice' => $end[2],
            'data' => $data,
        ];

        return json($re1);
    }
}
