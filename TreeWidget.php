<?php

namespace backend\widgets;

use yii\base\Widget;
use yii\base\Exception;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Tree
 * 
 * @author 风居住的地方
 * @email 819434425@qq.com
 * 
 * 传递的数组格式，关联数组就可以
 * 
 */
class TreeWidget extends Widget {

    /**
     * 表单主题样式
     * FORM_WHITE   浅色透明
     * FORM_BLACK   灰色透明
     * FORM_TILE    白色不透明
     */
    const FORM_WHITE = 'tile color transparent-white main-con';
    const FORM_BLACK = 'tile color transparent-black main-con';
    const FORM_TILE = 'tile main-con';
    
    /**
     * 主题样式
     * @var type 
     */
    public $theme = self::FORM_WHITE;
    
    /**
     * 表格标题
     * @var type 
     */
    public $title = '数据列表';
    
    /**
     * CArrayDataProvider 数据对象或数组数据
     * 组件数据接收参数(关联数组)     
     * @var Object || array
     */
    public $dataProvider;

    /**
     * 指定主键字段
     * @var type 
     */
    public $pk = 'id';

    /**
     * 赋值接收数据
     * @var type 
     */
    public $arrAll = [];

    /**
     * 按_ID作键名的多维关系
     * @var type 
     */
    public $arrIdRelation = [];

    /**
     * 多选框的选项
     * @var type 
     */
    public $checkboxOption = [];

    /**
     * 按_ID作键名的多维关系的简化,用来输出树状图
     * @var type 
     */
    public $arrIdRelationSimple = [];

    /**
     * 将原始数据转化成的_ID作键名的数组
     * @var type 
     */
    public $arrIdAll = [];

    /**
     * 所有的父子关系
     * @var type 
     */
    public $arrIdSon = [];

    /**
     * 叶子节点的_ID
     * @var type 
     */
    public $arrIdLeaf = [];

    /**
     * 根节点的_ID
     * @var type 
     */
    public $arrIdRoot = [];

    /**
     * 每个节点下的子孙后代_ID
     * @var type 
     */
    public $arrIdChildren = [];

    /**
     * 每个节点回逆到根
     * @var type 
     */
    public $arrIdBackPath = [];

    /**
     * 输出树的结构
     * @var type 
     */
    public $strItem = '<br />{$strSep}{$name}';

    /**
     * 数据字段参数数组
     * @var type 
     */
    public $dataKey = [];

    /**
     * 表格选项
     * @var type 
     */
    public $tableOptions = ['class' => 'table table-bordered table-striped table-hover'];

    /**
     * 指定需要格式化的字段
     * @var type 
     */
    public $formatParam = 'name';

    /**
     * 表格列名称
     * @var type 
     */
    public $tableHead = [];

    /**
     * 表格最后一列的名称
     * @var type 
     */
    public $lastTableHead = '操作';

    /**
     * 操作列的按钮
     * @var type 
     */
    private $_actionLink = [];

    /**
     * 父ID
     * @var type 
     */
    public $pid = 'pid';

    /**
     * 指定树的类型
     * true 表格类型树
     * false 下拉框类型树
     * @var type 
     */
    public $treeType = true;

    /**
     * 绑定下拉框value值
     * @var type 
     */
    public $optionValue = 'id';

    /**
     * 格式化时间
     * @var type 
     */
    public $formatTime = array();

    /**
     * 下拉框样式
     * @var type 
     */
    public $selectClass = 'class="span3"';

    /**
     * 下拉框的name名称
     * @var type 
     */
    public $selectName = '';

    /**
     * 设置下拉框的默认值和选项
     * @var type 
     */
    public $defaultSelectValue = array(
        0, '≡ 作为一级栏目 ≡',
    );

    /**
     * 设置下拉框是否多选
     * true 多选
     * false 单选
     * @var type 
     */
    public $isMultiple = false;

    /**
     * 绑定到下拉框的默认值
     * @var type 
     */
    public $bindSelectValue = 0;

    /**
     * 操作列
     * @var type 
     */
    public $action = array();

    /**
     * 绑定到多选框属性的值
     * @var type 
     */
    public $bindCheckboxData = array();

    /**
     * 默认选中的多选框的值
     * @var type 
     */
    public $defaultCheckedValue = array();
    public $checked = '{checked}';

    /**
     * 用法
     * <div class="yy-checkbox-combat"><div class="yy-checkbox-center">{checkTemplate}</div></div>
     * @var type 
     */
    public $checkHtml;

    /**
     * 模板变量
     * @var type 
     */
    public $checkTemplate = '{checkTemplate}';

    public function init() {
        if (isset($this->tableOptions['class'])) {
            $this->tableOptions['class'] .= ' table table-bordered table-striped table-hover table-left';
        } else {
            $this->tableOptions['class'] = 'table table-bordered table-striped table-hover table-left';
        }
    }
    
    /**
     * 运行
     */
    public function run() {
        $html = '';
        $html .= '<section class="'.$this->theme.'">';
        $html .= $this->renderTitle();
        $html .= '<div class="tile-body nopadding">';
        $html .= '<div class="table-responsive">';
        if (is_array($this->dataProvider) && count($this->dataProvider) > 0) {
            $html .= $this->_run($this->dataProvider);
        } else {
            throw new Exception('$this->dataProvider 类型错误，需要传入数组,当前为非数组或空数组!', 500);
        }
        $html .= '</div></div>';
        $html .= '</section>';
        
        if (!empty($html)) {
            echo $html;
        } else {   
            echo '<tr><td colspan="4" class="empty"><span class="empty">没有找到数据.</span></td></tr>';
        }
    }

    /**
     * 运行
     * @param type $datas
     * @return type
     */
    private function _run($datas) {
        foreach ($datas as $data) {

            if (!empty($this->action) && count($this->action) > 0) {
                foreach ($this->action as $key => $action) {
                    if (isset($action['visible'])) {
                        $visible = $action['visible'] instanceof \Closure
                                ? call_user_func($action['visible'])
                                : $action['visible'];
                    } else {
                        $visible = false;
                    }
                    if (!$visible) {
                        unset($this->action[$key]);
                        continue;
                    }
                    $k = array_keys($action['url']);
                    $k = '{$' . $k[0] . '}';
                    $this->_actionLink[$k] = '';
                }
                $this->dataKey = array_keys($data);
            }
            $this->arrAll[] = $data;
        }
        $this->processData();
        if ($this->treeType === true)
            $data = $this->getTable();
        else
            $data = $this->getSelect($this->selectName, $this->bindSelectValue, $this->isMultiple, $this->selectClass, $this->defaultSelectValue);

        return $data;
    }
    
    /**
     * 渲染标题
     */
    public function renderTitle() {
        if(is_null($this->title)){
            return '';
        }

        $controls = '<div class="controls">
                <a href="#" class="minimize"><i class="fa fa-chevron-down"></i></a>
                    <a href="#" class="remove">
                            <i class="fa fa-times">
                            </i>
                    </a>
                    <a href="#" class="refresh"><i class="fa fa-refresh"></i></a>
                </div>';

        if(!$this->title){
            return '<div class="tile-header">
                '.$controls.'
            </div>';
        }

        return '<div class="tile-header">
                    <h1>
                        <strong>
                            ' . $this->title . '
                        </strong>
                    </h1>
                    '.$controls.'
                </div>';
    }

    /**
     * 获得html
     * @return type
     */
    public function getHtml() {
        return $this->genHtml();
    }

    /**
     * 设置分层字段
     * 表格类型
     * @return string
     */
    public function getItemName() {
        $html = '<tr>';
        if (isset($this->checkboxOption['type']))
            unset($this->checkboxOption['type']);

        if (isset($this->checkboxOption['value']))
            unset($this->checkboxOption['value']);

        if (!empty($this->defaultCheckedValue)) {
            $this->checked = '{checked}';
        } else {
            $this->checked = '';
        }

        foreach ($this->dataKey as $v) {
            if ($this->formatParam == $v) {
                if (!empty($this->checkboxOption) && !empty($this->bindCheckboxData)) {
                    if (!empty($this->checkHtml) && !empty($this->checkTemplate)) {
                        $this->checkHtml = addcslashes($this->checkHtml, '"');
                        $str = '{$strSep}&nbsp;<input ' . $this->checked . ' type=\"checkbox\" ' . self::setAttributes($this->checkboxOption) . ' ' . self::setAttr($this->bindCheckboxData) . '/>&nbsp;';

                        $str = str_replace($this->checkTemplate, $str, $this->checkHtml);
                    } else {
                        $str = '{$strSep}&nbsp;<input ' . $this->checked . ' type=\"checkbox\" ' . self::setAttributes($this->checkboxOption) . ' ' . self::setAttr($this->bindCheckboxData) . '/>&nbsp;';
                    }
                } else
                    $str = '{$strSep}';
            } else
                $str = '';

            $html .= '<td>' . $str . '{$' . $v . '}</td>';
        }
        if (!empty($this->_actionLink) && $ks = array_keys($this->_actionLink)) {
            $html .= '<td>' . implode(' &nbsp; ', $ks) . '</td>';
        } else {
            $html .= '<td>&nbsp;</td>';
        }

        $html .= '</tr>';
        return $html;
    }

    /**
     * 获取表格列名称
     * @return string
     */
    public function getTableHead() {
        $html = '<tr>';
        foreach ($this->tableHead as $v)
            $html .= '<th>' . $v . '</th>';

        $html .= '<th>' . $this->lastTableHead . '</th>';
        $html .= '</tr>';
        return $html;
    }

    /**
     * 获得表格形式的树
     * @return string
     */
    public function getTable() {
        $this->strItem = $this->getItemName();
        $strRe = '<table ' . self::renderAttributes($this->tableOptions) . '>';
        $strRe .= '<thead>' . $this->getTableHead() . '</thead><tbody>';
        $strRe .= $this->parseData($this->genHtml());
        $strRe .= '</tbody></table>';
        return $strRe;
    }

    public function parseData($data) {
        if (!is_array($data))
            return $data;

        $html = '';
        foreach ($data as $k => $v) {
            if (!is_array($v)) {
                $html .= $v;
            } else {
                $html .= $this->parseData($v);
            }
        }
        return $html;
    }

    /**
     * 获取下拉框形式的树
     * @param type $strName
     * @param array $arrValue
     * @param type $blmMulti
     * @param type $strExt
     * @param type $arrFirst
     * @return string
     */
    public function getSelect($strName = 'tree', $arrValue = array(), $blmMulti = false, $strExt = '', $arrFirst = null) {
        !is_array($arrValue) && $arrValue = array($arrValue);
        foreach ($this->arrIdAll as $strTemp => $arrTemp) {
            $this->arrIdAll[$strTemp]['selected'] = '';

            if (in_array($arrTemp[$this->pk], $arrValue)) {
                $this->arrIdAll[$strTemp]['selected'] = ' selected="selected"';
            }
        }
        $this->strItem = '<option value=\"{$' . $this->optionValue . '}\"{$selected} title=\"{$' . $this->formatParam . '}\">{$strSep}{$' . $this->formatParam . '}</option>';
        $strRe = '<select id="id_' . $strName . '" name="' . $strName . ($blmMulti ? '[]' : '') . '"';
        $strRe .= ($blmMulti ? ' multiple="multiple"' : '') . (empty($strExt) ? '' : ' ' . $strExt) . '>';

        if (is_array($arrFirst) && count($arrFirst) == 2) {
            $strRe .= '<option value="' . $arrFirst[0] . '">' . $arrFirst[1] . '</option>';
        }

        $strRe .= $this->getHtml() . '</select>';
        return $strRe;
    }

    /**
     * 数据处理
     * @param type $arrData
     * @return type
     */
    private function helpForGetRelation($arrData) {
        $arrRe = array();
        foreach ($arrData as $strTemp => $arrTemp) {
            $arrRe[$strTemp] = $arrTemp;
            if (isset($this->arrIdRelation[$strTemp])) {
                $arrRe[$strTemp] = $this->arrIdRelation[$strTemp];
            }
            if (count($arrRe[$strTemp]) > 0) {
                $arrRe[$strTemp] = $this->helpForGetRelation($arrRe[$strTemp]);
            } else {
                array_push($this->arrIdLeaf, $strTemp);
            }
        }
        return $arrRe;
    }

    /**
     * 数据处理
     * @param type $arrData
     * @return type
     */
    private function helpForGetChildren($arrData) {
        $arrRe = array_keys($arrData);
        foreach ($arrData as $arrTemp) {
            $arrRe = array_merge($arrRe, $this->helpForGetChildren($arrTemp));
        }
        return $arrRe;
    }

    /**
     * 数据处理
     * @param type $str
     * @return type
     */
    private function helpForGetBackPath($str) {
        $arrRe = array();
        $intTemp = isset($this->arrIdAll[$str][$this->pid]) ? $this->arrIdAll[$str][$this->pid] : 0;
        if ($intTemp > 0) {
            $intTemp = '_' . $intTemp;
            array_push($arrRe, $intTemp);
            $arrRe = array_merge($arrRe, $this->helpForGetBackPath($intTemp));
        }
        return $arrRe;
    }

    /**
     * 数据处理
     */
    private function processData() {
        $count = count($this->arrAll);
        foreach ($this->arrAll as $arrTemp) {
            $strTemp = '_' . $arrTemp[$this->pk];
            $this->arrIdAll[$strTemp] = $arrTemp;
            if ($arrTemp[$this->pid] > 0 && $count > 1) {
                $strTemp_ = '_' . $arrTemp[$this->pid];
                !isset($this->arrIdRelation[$strTemp_]) && $this->arrIdRelation[$strTemp_] = array();
                $this->arrIdRelation[$strTemp_][$strTemp] = array();
                !isset($this->arrIdSon[$strTemp_]) && $this->arrIdSon[$strTemp_] = array();
                array_push($this->arrIdSon[$strTemp_], $strTemp);
            } else {
                !isset($this->arrIdRelation[$strTemp]) && $this->arrIdRelation[$strTemp] = array();
                array_push($this->arrIdRoot, $strTemp);
            }
        }

        $this->arrIdRelation = $this->helpForGetRelation($this->arrIdRelation);
        $this->arrIdLeaf = array_unique($this->arrIdLeaf);
        foreach ($this->arrIdRelation as $strTemp => $arrTemp) {
            $this->arrIdChildren[$strTemp] = $this->helpForGetChildren($arrTemp);
            in_array($strTemp, $this->arrIdRoot) && $this->arrIdRelationSimple[$strTemp] = $arrTemp;
        }
        $arrTemp = array_keys($this->arrIdAll);
        foreach ($arrTemp as $strTemp) {
            $this->arrIdBackPath[$strTemp] = $this->helpForGetBackPath($strTemp);
        }
    }

    /**
     * 数据处理
     * @param type $intLen
     * @return string
     */
    private function genSeparator($intLen) {
        $strRe = '';
        $i = 0;
        while ($i < $intLen) {
            $strRe .= '　' . (($i + 1 == $intLen) ? '├' : '│');
            $i++;
        }
        !empty($strRe) && $strRe .= '─';
        return $strRe;
    }

    /**
     * 数据处理
     * @param type $arrRelation
     * @param type $intSep
     * @return type
     */
    private function genHtml($arrRelation = null, $intSep = 0) {
        $strRe = '';
        $newData = array();
        null === $arrRelation && $arrRelation = $this->arrIdRelationSimple;

        foreach ($arrRelation as $strKey => $arrTemp) {
            if (count($this->arrIdAll[$strKey]) > 0) {
                if (!empty($this->formatTime) && count($this->formatTime) > 0) {
                    foreach ($this->formatTime as $formatTime) {
                        if ($this->arrIdAll[$strKey][$formatTime] > 0) {
                            $this->arrIdAll[$strKey][$formatTime] = date('Y-m-d H:i:s', $this->arrIdAll[$strKey][$formatTime]);
                        }
                    }
                }
                if (!empty($this->action) && count($this->action) > 0) {
                    foreach ($this->action as $key => $action) {
                        if (isset($action['options'])) {
                            
                            foreach($action['options'] as &$opt) {
                                
                                preg_match_all('/\{(.*?)\}/', $opt, $matches);
                                
                                if (!empty($matches[1])) {
                                    $matches = array_unique($matches[1]);

                                    foreach($matches as $match) {

                                            if (strpos($opt , '{'.$match.'}') !== false) {
                                                $opt = str_replace('{'.$match.'}', $this->arrIdAll[$strKey][$match], $opt);
                                            }
                                    }
                                }
                            }
                        }
                        
                        $k = array_keys($action['url']);
                        $url = eval("return '" . $action['url'][$k[0]] . "';");
                        if (isset($action['urlParams']) && count($action['urlParams']) > 0) {
                            $url .= '?';
                            foreach ($action['urlParams'] as $urlParams) {
                                if (isset($this->arrIdAll[$strKey][$urlParams])) {
                                    $url .= $urlParams . '=' . $this->arrIdAll[$strKey][$urlParams] . '&';
                                } else {
                                    preg_match('/\{(.*?)\}/', $urlParams, $matches);
                                    $url .= str_replace($matches[0], '' , $urlParams) . urlencode($this->arrIdAll[$strKey][$matches[1]]);
                                }
                            }
                        }
                        $url = substr($url, 0, -1);
                        
                        $this->arrIdAll[$strKey][$k[0]] = self::link($action['label'], $url, isset($action['options']) ? $action['options'] : array());
                        ;
                    }
                }
                $strSep = $this->genSeparator($intSep);
                extract($this->arrIdAll[$strKey]);
                if (!empty($this->defaultCheckedValue)) {
                    eval('$h = "' . $this->strItem . '";');

                    foreach ($this->defaultCheckedValue as $value) {
                        if (isset($id)) {
                            if ($id == $value) {
                                $h = str_replace($this->checked, 'checked', $h);
                            }
                        }
                    }
                    $newData[] = $h;
                    count($arrTemp) > 0 && $newData[] = $this->genHtml($arrTemp, ($intSep + 1));
                } else {
                    eval('$strRe .= "' . $this->strItem . '";');
                    count($arrTemp) > 0 && $strRe .= $this->genHtml($arrTemp, ($intSep + 1));
                }
            }
        }
        return !empty($this->defaultCheckedValue) ? $newData : $strRe;
    }

    protected static function link($label, $url, $options) {
        return '<a href="' . $url . '" ' . self::renderAttributes($options) . '>' . $label . '</a>';
    }

    public static function setAttr($options) {
        if (empty($options))
            return '';


        $html = '';
        foreach ($options as $name => $value)
            $html .= ' ' . $name . '=\"{$' . $value . '}\"';

        return $html;
    }

    /**
     * 连接html的属性
     * @param type $htmlOptions     html属性选项
     * @author wangjiacheng
     */
    public static function setAttributes($htmlOptions) {
        if (empty($htmlOptions))
            return '';

        $html = '';
        foreach ($htmlOptions as $name => $value)
            $html .= ' ' . $name . '=\"' . $value . '\"';

        return $html;
    }

    /**
     * 连接html的属性
     * @param type $htmlOptions     html属性选项
     * @author wangjiacheng
     */
    public static function renderAttributes($htmlOptions) {
        if (empty($htmlOptions))
            return '';

        $html = '';
        foreach ($htmlOptions as $name => $value)
            $html .= ' ' . $name . '="' . $value . '"';

        return $html;
    }
}

// 使用方法

echo \backend\widgets\TreeWidget::widget([
    'dataProvider' => $menus,
    'pid' => 'pid',
    'tableOptions' => ['id' => 'menu'],
    'formatParam' => 'name',
    'tableHead' => [
        '菜单id',
        '菜单名称',
        '菜单类名',
        '菜单方法名',
        '父id',
        '状态',
        '是否菜单',
    ],
    'lastTableHead' => '操作',
    'action' => [
        [
            'label' => '<i class="fa fa-pencil"></i>',
            'url' => [
                'edit'=>'/auth-menu/edit',
            ],
            'urlParams' => ['id'],
            'options' => [
                'class' => Color::getBthClass(Color::COLOR_DEFAULT),
                'title' => '编辑'
            ]
        ],
        [
            'label' => '<i class="fa fa-trash-o"></i>',
            'url' => [
                'add' => '/auth-menu/delete',
            ],
            'urlParams' => ['id'],
            'options' => [
                'onclick' => "return confirm('确认要删除吗?');",
                'class' => Color::getBthClass(Color::COLOR_DEFAULT),
                'title' => '删除'
            ]
        ],
    ],  
]);
