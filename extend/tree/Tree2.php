<?php

namespace tree;
/**
 * 通用的树型类，可以生成任何树型结构
 */
class Tree2
{

    /**
     * 生成树型结构所需要的2维数组
     * @var array
     */
    public $arr = [];

    /**
     * 生成树型结构所需修饰符号，可以换成图片
     * @var array
     */
    public $icon = ['│', '├', '└'];
    public $nbsp = "&nbsp;";
    private $str = '';
    /**
     * @access private
     */
    public $ret = '';

    /**
     * 构造函数，初始化类
     * @param array 2维数组，例如：
     * array(
     *      1 => array('id'=>'1','tjid'=>0,'name'=>'一级栏目一'),
     *      2 => array('id'=>'2','tjid'=>0,'name'=>'一级栏目二'),
     *      3 => array('id'=>'3','tjid'=>1,'name'=>'二级栏目一'),
     *      4 => array('id'=>'4','tjid'=>1,'name'=>'二级栏目二'),
     *      5 => array('id'=>'5','tjid'=>2,'name'=>'二级栏目三'),
     *      6 => array('id'=>'6','tjid'=>3,'name'=>'三级栏目一'),
     *      7 => array('id'=>'7','tjid'=>3,'name'=>'三级栏目二')
     *      )
     * @return array
     */
    public function init($arr = [])
    {
        $this->arr = $arr;
        $this->ret = '';
        return is_array($arr);
    }

    /**
     * 得到父级数组
     * @param int
     * @return array
     */
    public function getParent($myId)
    {
        $newArr = [];
        if (!isset($this->arr[$myId]))
            return false;
        $pid = $this->arr[$myId]['tjid'];
        $pid = $this->arr[$pid]['tjid'];
        if (is_array($this->arr)) {
            foreach ($this->arr as $id => $a) {
                if ($a['tjid'] == $pid)
                    $newArr[$id] = $a;
            }
        }
        return $newArr;
    }

    /**
     * 得到子级数组
     * @param int
     * @return array
     */
    public function getChild($myId)
    {
        $newArr = [];
        if (is_array($this->arr)) {
            foreach ($this->arr as $id => $a) {

                if ($a['tjid'] == $myId) {
                    $newArr[$id] = $a;
                }
            }
        }

        return $newArr ? $newArr : false;
    }

    /**
     * 得到当前位置数组
     * @param int
     * @return array
     */
    public function getPosition($myId, &$newArr)
    {
        $a = [];
        if (!isset($this->arr[$myId]))
            return false;
        $newArr[] = $this->arr[$myId];
        $pid      = $this->arr[$myId]['tjid'];
        if (isset($this->arr[$pid])) {
            $this->getPosition($pid, $newArr);
        }
        if (is_array($newArr)) {
            krsort($newArr);
            foreach ($newArr as $v) {
                $a[$v['id']] = $v;
            }
        }
        return $a;
    }

    /**
     * 得到树型结构
     * @param int ID，表示获得这个ID下的所有子级
     * @param string 生成树型结构的基本代码，例如："<option value=\$id \$selected>\$spacer\$name</option>"
     * @param int 被选中的ID，比如在做树型下拉框的时候需要用到
     * @return string
     */
    public function getTree($myId, $str, $sid = 0, $adds = '', $str_group = '')
    {
        $number = 1;
        //一级栏目
        $child = $this->getChild($myId);

        if (is_array($child)) {
            $total = count($child);

            foreach ($child as $key => $value) {
                $j = $k = '';
                if ($number == $total) {
                    $j .= $this->icon[2];
                } else {
                    $j .= $this->icon[1];
                    $k = $adds ? $this->icon[0] : '';
                }
                $spacer   = $adds ? $adds . $j : '';
                $selected = $value['id'] == $sid ? 'selected' : '';
                $id       = 0;
                $nstr     = '';
                @extract($value);

                $parentId = $value['tjid'];


                $parentId == 0 && $str_group ? eval("\$nstr = \"$str_group\";") : eval("\$nstr = \"$str\";");

                $this->ret .= $nstr;
                $nbsp      = $this->nbsp;
                $this->getTree($id, $str, $sid, $adds . $k . $nbsp, $str_group);
                $number++;
            }
        }
        return $this->ret;
    }

    /**
     * 生成树型结构数组
     * @param int myID，表示获得这个ID下的所有子级
     * @param int $maxLevel 最大获取层级,默认不限制
     * @param int $level 当前层级,只在递归调用时使用,真实使用时不传入此参数
     * @return array
     */
    public function getTreeArray($myId, $maxLevel = 0, $level = 1)
    {
        $returnArray = [];

        //一级数组
        $children = $this->getChild($myId);

        if (is_array($children)) {
            foreach ($children as $child) {
                $child['_level']           = $level;
                $returnArray[$child['id']] = $child;
                if ($maxLevel === 0 || ($maxLevel !== 0 && $maxLevel > $level)) {

                    $mLevel                                = $level + 1;
                    $returnArray[$child['id']]["children"] = $this->getTreeArray($child['id'], $maxLevel, $mLevel);
                }

            }
        }

        return $returnArray;
    }
	
	/**
     * 生成树型结构数组
     * @param int myID，表示获得这个ID下的所有子级
     * @param int $maxLevel 最大获取层级,默认不限制
     * @param int $level 当前层级,只在递归调用时使用,真实使用时不传入此参数
     * @return array
     */
    public function getTreeArray2($myId, $maxLevel = 0, $level = 1)
    {
        $returnArray = [];

        //一级数组
        $children = $this->getChild($myId);

        if (is_array($children)) {
            foreach ($children as $child) {
                $child['_level']           = $level;
                $mchild = [];
                //$returnArray[$child['id']]['value'] = $child['id'];
				//$returnArray[$child['id']]['text'] = $child['name'];
				
                if ($maxLevel === 0 || ($maxLevel !== 0 && $maxLevel > $level)) {

                    $mLevel                                = $level + 1;
                    //$returnArray[$child['id']]["children"] = $this->getTreeArray2($child['id'], $maxLevel, $mLevel);
					$mchild  = $this->getTreeArray2($child['id'], $maxLevel, $mLevel);
				}
				
				//$returnArray[]=array('value'=>$child['id'],'text'=>$child['name'],'children'=>$mchild);
                $returnArray[]=array('name'=>'第'.$level.'代--'.$child['name'],'children'=>$mchild);

            }
        }

        return $returnArray;
    }

    /**
     * 生成树型结构数组
     * @param int myID，表示获得这个ID下的所有子级
     * @param int $maxLevel 最大获取层级,默认不限制
     * @param int $level 当前层级,只在递归调用时使用,真实使用时不传入此参数
     * @return array
     */
    public function getTreeArray4($myId,$level = 2)
    {
         $returnArray = [];
        //一级数组
        $children = $this->getChild($myId);
        if (is_array($children)) {
            foreach ($children as $child) {

                $mLevel = $level + 1;
                $mchild  = $this->getTreeArray4($child['id'],$mLevel);

               $returnArray[]=array('name'=>$child['name'],'ceng'=>$level,'ztnum'=>$child['ztnum'],'level'=>$child['level'],'id'=>$child['id'],'jointime'=>$child['jointime'],'status'=>$child['status'],'children'=>$mchild);

            }
        }
        return  $returnArray;

    }

    /**
     * 生成树型结构数组
     * @param int myID，表示获得这个ID下的所有子级
     * @param int $maxLevel 最大获取层级,默认不限制
     * @param int $level 当前层级,只在递归调用时使用,真实使用时不传入此参数
     * @return array
     */
    public function getTreeArray5($myId,$level = 2)
    {
        $returnArray = [];
        //一级数组
        $children = $this->getChild($myId);
        if (is_array($children)) {
            foreach ($children as $child) {

                $mLevel = $level + 1;
                $mchild  = $this->getTreeArray5($child['id'],$mLevel);

                $returnArray[]=array('name'=>$child['name'],'id'=>$child['id'],'image'=>$child['image'],'description'=>$child['description'] ,'ceng'=>$level, 'status'=>$child['status'], 'children'=>$mchild);

            }
        }
        return  $returnArray;

    }


	
	/**
     * jinzhi_blog 前端数据
     * @param int $level 当前层级,只在递归调用时使用,真实使用时不传入此参数
     * @return array
     */
    public function getTreeArray3($myId)
    {
        $returnArray = [];

        //一级数组
        $children = $this->getChild($myId);
		
		

        if (is_array($children)) {
            foreach ($children as $child) {//省
                
                $returnArray['QS_city_parent'][]=$child['id'].','.$child['name'];
				
				$mchild = $this->getChild($child['id']); 
					if (is_array($mchild)) {
						 foreach ($mchild as $child_2) {//市
							$returnArray['QS_city'][$child['id']][]=$child_2['id'].','.$child_2['name'];
							 
							 $mmchild = $this->getChild($child_2['id']); 
							 if (is_array($mmchild)) {
								 foreach ($mmchild as $child_3) {//县
									 
									 
									 $returnArray['QS_city'][$child_2['id']][]=$child_3['id'].','.$child_3['name'];
							 
									}
				
							}
					}
				}
            }
        }

        return $returnArray;
    }

    /**
     * 同上一方法类似,但允许多选
     */
    public function getTreeMulti($myId, $str, $sid = 0, $adds = '')
    {
        $number = 1;
        $child  = $this->getChild($myId);
        if (is_array($child)) {
            $total = count($child);
            foreach ($child as $id => $a) {
                $j = $k = '';
                if ($number == $total) {
                    $j .= $this->icon[2];
                } else {
                    $j .= $this->icon[1];
                    $k = $adds ? $this->icon[0] : '';
                }
                $spacer = $adds ? $adds . $j : '';

                $selected = $this->have($sid, $id) ? 'selected' : '';
                @extract($a);
                eval("\$nstr = \"$str\";");
                $this->ret .= $nstr;
                $this->getTreeMulti($id, $str, $sid, $adds . $k . '&nbsp;');
                $number++;
            }
        }
        return $this->ret;
    }

    /**
     * @param integer $myId 要查询的ID
     * @param string $str 第一种HTML代码方式
     * @param string $str2 第二种HTML代码方式
     * @param integer $sid 默认选中
     * @param integer $adds 前缀
     */
    public function getTreeCategory($myId, $str, $str2, $sid = 0, $adds = '')
    {
        $number = 1;
        $child  = $this->getChild($myId);
        if (is_array($child)) {
            $total = count($child);
            foreach ($child as $id => $a) {
                $j = $k = '';
                if ($number == $total) {
                    $j .= $this->icon[2];
                } else {
                    $j .= $this->icon[1];
                    $k = $adds ? $this->icon[0] : '';
                }
                $spacer = $adds ? $adds . $j : '';

                $selected = $this->have($sid, $id) ? 'selected' : '';
                @extract($a);
                if (empty($html_disabled)) {
                    eval("\$nstr = \"$str\";");
                } else {
                    eval("\$nstr = \"$str2\";");
                }
                $this->ret .= $nstr;
                $this->getTreeCategory($id, $str, $str2, $sid, $adds . $k . '&nbsp;');
                $number++;
            }
        }
        return $this->ret;
    }

    /**
     * 同上一类方法，jquery treeview 风格，可伸缩样式（需要treeview插件支持）
     * @param $myId 表示获得这个ID下的所有子级
     * @param $effected_id 需要生成treeview目录数的id
     * @param $str 末级样式
     * @param $str2 目录级别样式
     * @param $showlevel 直接显示层级数，其余为异步显示，0为全部限制
     * @param $style 目录样式 默认 filetree 可增加其他样式如'filetree treeview-famfamfam'
     * @param $currentlevel 计算当前层级，递归使用 适用改函数时不需要用该参数
     * @param $recursion 递归使用 外部调用时为FALSE
     * @return string
     */
    function getTreeView($myId, $effected_id = 'example', $str = "<span class='file'>\$name</span>", $str2 = "<span class='folder'>\$name</span>", $showlevel = 0, $style = 'filetree ', $currentlevel = 1, $recursion = FALSE)
    {
        $child = $this->getChild($myId);
        if (!defined('EFFECTED_INIT')) {
            $effected = ' id="' . $effected_id . '"';
            define('EFFECTED_INIT', 1);
        } else {
            $effected = '';
        }
        $placeholder = '<ul><li><span class="placeholder"></span></li></ul>';
        if (!$recursion)
            $this->str .= '<ul' . $effected . '  class="' . $style . '">';
        foreach ($child as $id => $a) {

            @extract($a);
            if ($showlevel > 0 && $showlevel == $currentlevel && $this->getChild($id))
                $folder = 'hasChildren'; //如设置显示层级模式@2011.07.01
            $floder_status = isset($folder) ? ' class="' . $folder . '"' : '';
            $this->str     .= $recursion ? '<ul><li' . $floder_status . ' id=\'' . $id . '\'>' : '<li' . $floder_status . ' id=\'' . $id . '\'>';
            $recursion     = FALSE;
            //判断是否为终极栏目
            if ($child == 1) {
                eval("\$nstr = \"$str2\";");
                $this->str .= $nstr;
                if ($showlevel == 0 || ($showlevel > 0 && $showlevel > $currentlevel)) {
                    $this->getTreeView($id, $effected_id, $str, $str2, $showlevel, $style, $currentlevel + 1, TRUE);
                } elseif ($showlevel > 0 && $showlevel == $currentlevel) {
                    $this->str .= $placeholder;
                }
            } else {
                eval("\$nstr = \"$str\";");
                $this->str .= $nstr;
            }
            $this->str .= $recursion ? '</li></ul>' : '</li>';
        }
        if (!$recursion)
            $this->str .= '</ul>';
        return $this->str;
    }

    /**
     * 同上一类方法，jquery treeview 风格，可伸缩样式（需要treeview插件支持）
     * @param $myId 表示获得这个ID下的所有子级
     * @param $effected_id 需要生成treeview目录数的id
     * @param $str 末级样式
     * @param $str2 目录级别样式
     * @param $showlevel 直接显示层级数，其余为异步显示，0为全部限制
     * @param $style 目录样式 默认 filetree 可增加其他样式如'filetree treeview-famfamfam'
     * @param $currentlevel 计算当前层级，递归使用 适用改函数时不需要用该参数
     * @param $recursion 递归使用 外部调用时为FALSE
     * @param $dropdown 有子元素时li的class
     */

    function getTreeViewMenu($myId, $effected_id = 'example', $str = "<span class='file'>\$name</span>", $str2 = "<span class='folder'>\$name</span>", $showlevel = 0, $ul_class = "", $li_class = "", $style = 'filetree ', $currentlevel = 1, $recursion = FALSE, $dropdown = 'hasChild')
    {
        $child = $this->getChild($myId);
        if (!defined('EFFECTED_INIT')) {
            $effected = ' id="' . $effected_id . '"';
            define('EFFECTED_INIT', 1);
        } else {
            $effected = '';
        }
        $placeholder = '<ul><li><span class="placeholder"></span></li></ul>';
        if (!$recursion) {
            $this->str .= '<ul' . $effected . '  class="' . $style . '">';
        }

        foreach ($child as $id => $a) {

            @extract($a);
            if ($showlevel > 0 && is_array($this->getChild($a['id']))) {
                $floder_status = " class='$dropdown $li_class'";
            } else {
                $floder_status = " class='$li_class'";;
            }
            $this->str .= $recursion ? "<ul class='$ul_class'><li  $floder_status id= 'menu-item-$id'>" : "<li  $floder_status   id= 'menu-item-$id'>";
            $recursion = FALSE;
            //判断是否为终极栏目
            if ($this->getChild($a['id'])) {
                eval("\$nstr = \"$str2\";");
                $this->str .= $nstr;
                if ($showlevel == 0 || ($showlevel > 0 && $showlevel > $currentlevel)) {
                    $this->getTreeViewMenu($a['id'], $effected_id, $str, $str2, $showlevel, $ul_class, $li_class, $style, $currentlevel + 1, TRUE);
                } elseif ($showlevel > 0 && $showlevel == $currentlevel) {
                    //$this->str .= $placeholder;
                }
            } else {
                eval("\$nstr = \"$str\";");
                $this->str .= $nstr;
            }
            $this->str .= $recursion ? '</li></ul>' : '</li>';
        }
        if (!$recursion)
            $this->str .= '</ul>';
        return $this->str;
    }

    /**
     * 获取子栏目json
     * Enter description here ...
     * @param unknown_type $myId
     */
    public function createSubJson($myId, $str = '')
    {
        $sub_cats = $this->getChild($myId);
        $n        = 0;
        if (is_array($sub_cats))
            foreach ($sub_cats as $c) {
                $data[$n]['id'] = iconv(CHARSET, 'utf-8', $c['catid']);
                if ($this->getChild($c['catid'])) {
                    $data[$n]['liclass']  = 'hasChildren';
                    $data[$n]['children'] = [['text' => '&nbsp;', 'classes' => 'placeholder']];
                    $data[$n]['classes']  = 'folder';
                    $data[$n]['text']     = iconv(CHARSET, 'utf-8', $c['catname']);
                } else {
                    if ($str) {
                        @extract(array_iconv($c, CHARSET, 'utf-8'));
                        eval("\$data[$n]['text'] = \"$str\";");
                    } else {
                        $data[$n]['text'] = iconv(CHARSET, 'utf-8', $c['catname']);
                    }
                }
                $n++;
            }
        return json_encode($data);
    }

    private function have($list, $item)
    {
        return (strpos(',,' . $list . ',', ',' . $item . ','));
    }

}

