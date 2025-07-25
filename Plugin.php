<?php
/**
 * Xi's Reading Time Plugin for Typecho
 * 
 * @package XiReadingTime
 * @author XiNian_dada
 * @version 1.0.1
 * @link https://www.hairuosky.cn
 * 
 * 该插件在文章内容上方显示预计阅读时间
 */
class XiReadingTime_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法
     * 
     * @access public
     * @return void
     */
    public static function activate()
    {
        // 添加样式表
        Typecho_Plugin::factory('Widget_Archive')->header = array('XiReadingTime_Plugin', 'addHeader');
        
        // 在内容输出前插入阅读时间
        Typecho_Plugin::factory('Widget_Abstract_Contents')->contentEx = array('XiReadingTime_Plugin', 'insertReadingTime');
    }

    /**
     * 禁用插件方法
     * 
     * @access public
     * @return void
     */
    public static function deactivate()
    {
    }

    /**
     * 插件配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        // 添加配置选项
        $readingSpeed = new Typecho_Widget_Helper_Form_Element_Text(
            'readingSpeed',
            null,
            '250',
            _t('阅读速度'),
            _t('每分钟阅读字数（默认250字/分钟）')
        );
        $form->addInput($readingSpeed);
        
        $prefixText = new Typecho_Widget_Helper_Form_Element_Text(
            'prefixText',
            null,
            '预计阅读时间：',
            _t('前缀文本'),
            _t('显示在阅读时间前的文本')
        );
        $form->addInput($prefixText);
        
        $suffixText = new Typecho_Widget_Helper_Form_Element_Text(
            'suffixText',
            null,
            '分钟',
            _t('后缀文本'),
            _t('显示在阅读时间后的文本')
        );
        $form->addInput($suffixText);
        
        $displayPosition = new Typecho_Widget_Helper_Form_Element_Radio(
            'displayPosition',
            array(
                'before' => _t('文章内容前'),
                'after' => _t('文章内容后')
            ),
            'before',
            _t('显示位置'),
            _t('阅读时间显示的位置')
        );
        $form->addInput($displayPosition);
        
        $excludedCategories = new Typecho_Widget_Helper_Form_Element_Text(
            'excludedCategories',
            null,
            '',
            _t('排除的分类'),
            _t('不显示阅读时间的分类ID（多个ID用逗号分隔）')
        );
        $form->addInput($excludedCategories);
        
        $layout = new Typecho_Widget_Helper_Layout();
        $layout->html(_t('<h3>样式设置:</h3><hr>'));
        $form->addItem($layout);
        
        // 背景样式
        $bgStyle = new Typecho_Widget_Helper_Form_Element_Radio(
            'bgStyle',
            array(
                'border-left' => _t('左侧边框'),
                'solid-border' => _t('实心边框'),
                'full-bg' => _t('完整背景'),
                'none' => _t('无背景')
            ),
            'border-left',
            _t('背景样式'),
            _t('选择阅读时间框的背景样式')
        );
        $form->addInput($bgStyle);
        
        // 背景颜色
        $bgColor = new Typecho_Widget_Helper_Form_Element_Text(
            'bgColor',
            null,
            '#f8f9fa',
            _t('背景颜色'),
            _t('十六进制颜色值，如 #f8f9fa')
        );
        $form->addInput($bgColor);
        
        // 边框颜色
        $borderColor = new Typecho_Widget_Helper_Form_Element_Text(
            'borderColor',
            null,
            '#4e73df',
            _t('边框颜色'),
            _t('用于边框的颜色，如 #4e73df')
        );
        $form->addInput($borderColor);
        
        // 图标颜色
        $iconColor = new Typecho_Widget_Helper_Form_Element_Text(
            'iconColor',
            null,
            '#4e73df',
            _t('图标颜色'),
            _t('时钟图标的颜色，如 #4e73df')
        );
        $form->addInput($iconColor);
        
        // 分钟数颜色
        $minutesColor = new Typecho_Widget_Helper_Form_Element_Text(
            'minutesColor',
            null,
            '#4e73df',
            _t('分钟数颜色'),
            _t('分钟数字的颜色，如 #4e73df')
        );
        $form->addInput($minutesColor);
        
        // 文字颜色
        $textColor = new Typecho_Widget_Helper_Form_Element_Text(
            'textColor',
            null,
            '#343a40',
            _t('文字颜色'),
            _t('主文本颜色，如 #343a40')
        );
        $form->addInput($textColor);
        
        // 详细信息颜色
        $detailColor = new Typecho_Widget_Helper_Form_Element_Text(
            'detailColor',
            null,
            '#6c757d',
            _t('详细信息颜色'),
            _t('字数统计等次要文本颜色，如 #6c757d')
        );
        $form->addInput($detailColor);
        
        // 字体大小
        $fontSize = new Typecho_Widget_Helper_Form_Element_Text(
            'fontSize',
            null,
            '14',
            _t('字体大小(px)'),
            _t('主文本字体大小，单位像素')
        );
        $form->addInput($fontSize);
        
        // 分钟数字体大小
        $minutesSize = new Typecho_Widget_Helper_Form_Element_Text(
            'minutesSize',
            null,
            '18',
            _t('分钟数字体大小(px)'),
            _t('分钟数显示的大小，单位像素')
        );
        $form->addInput($minutesSize);
        
        // 圆角大小
        $borderRadius = new Typecho_Widget_Helper_Form_Element_Text(
            'borderRadius',
            null,
            '3',
            _t('圆角大小(px)'),
            _t('阅读时间框的圆角大小')
        );
        $form->addInput($borderRadius);
        
        // 是否显示详细信息
        $showDetails = new Typecho_Widget_Helper_Form_Element_Radio(
            'showDetails',
            array('1' => _t('显示'), '0' => _t('隐藏')),
            '1',
            _t('是否显示详细信息'),
            _t('是否显示字数统计和阅读速度')
        );
        $form->addInput($showDetails);
    }

    /**
     * 个人用户的配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {
    }

    /**
     * 添加头部资源
     * 
     * @access public
     * @return void
     */
    public static function addHeader()
    {
        $options = Helper::options();
        $pluginUrl = $options->pluginUrl . '/XiReadingTime/assets/';
        echo '<link rel="stylesheet" href="' . $pluginUrl . 'style.css" />';
    }

    /**
     * 插入阅读时间
     * 
     * @access public
     * @param string $content 文章内容
     * @param Widget_Abstract_Contents $widget 文章组件
     * @return string
     */
    public static function insertReadingTime($content, $widget)
    {
        // 只在文章和页面显示
        if (!$widget->is('single')) {
            return $content;
        }
        
        // 获取插件配置
        $config = Helper::options()->plugin('XiReadingTime');
        if (!$config || !isset($config->readingSpeed)) {
            return $content;
        }
        // 如果配置未初始化，使用默认值
        if (!$config) {
            return $content;
        }
        
        // 安全获取配置值
        $readingSpeed = isset($config->readingSpeed) ? intval($config->readingSpeed) : 250;
        $prefixText = isset($config->prefixText) ? $config->prefixText : '预计阅读时间：';
        $suffixText = isset($config->suffixText) ? $config->suffixText : '分钟';
        $displayPosition = isset($config->displayPosition) ? $config->displayPosition : 'before';
        $excludedCategories = isset($config->excludedCategories) ? $config->excludedCategories : '';
        
        // 检查是否在排除的分类中
        $excludedCats = [];
        if (!empty($excludedCategories)) {
            $excludedCats = array_map('trim', explode(',', $excludedCategories));
        }
        
        if (!empty($excludedCats)) {
            $currentCategory = isset($widget->categories[0]['mid']) ? $widget->categories[0]['mid'] : null;
            if ($currentCategory && in_array($currentCategory, $excludedCats)) {
                return $content;
            }
        }
        
        // 计算阅读时间
        $readingSpeed = $readingSpeed ?: 250; // 默认250字/分钟
        $cleanContent = self::cleanContent($content);
        $wordCount = mb_strlen($cleanContent, 'UTF-8');
        $readingTime = ceil($wordCount / $readingSpeed);
        $readingTime = max(1, $readingTime); // 至少1分钟
        
        // 安全获取样式配置
        $bgStyle = isset($config->bgStyle) ? $config->bgStyle : 'border-left';
        $bgColor = isset($config->bgColor) ? $config->bgColor : '#f8f9fa';
        $borderColor = isset($config->borderColor) ? $config->borderColor : '#4e73df';
        $iconColor = isset($config->iconColor) ? $config->iconColor : '#4e73df';
        $minutesColor = isset($config->minutesColor) ? $config->minutesColor : '#4e73df';
        $textColor = isset($config->textColor) ? $config->textColor : '#343a40';
        $detailColor = isset($config->detailColor) ? $config->detailColor : '#6c757d';
        $fontSize = isset($config->fontSize) ? $config->fontSize : '14';
        $minutesSize = isset($config->minutesSize) ? $config->minutesSize : '18';
        $borderRadius = isset($config->borderRadius) ? $config->borderRadius : '3';
        $showDetails = isset($config->showDetails) ? $config->showDetails : '1';
        
        // 构建内联样式
        $boxStyle = "font-size: {$fontSize}px;";
        $boxStyle .= "color: {$textColor};";
        $boxStyle .= "border-radius: {$borderRadius}px;";
        
        // 根据背景样式应用不同的样式
        switch ($bgStyle) {
            case 'border-left':
                $boxStyle .= "background-color: {$bgColor};";
                $boxStyle .= "border-left: 3px solid {$borderColor};";
                break;
            case 'solid-border':
                $boxStyle .= "background-color: {$bgColor};";
                $boxStyle .= "border: 1px solid {$borderColor};";
                break;
            case 'full-bg':
                $boxStyle .= "background-color: {$bgColor};";
                break;
            case 'none':
                $boxStyle .= "background-color: transparent;";
                $boxStyle .= "box-shadow: none;";
                break;
        }
        
        // 构建阅读时间HTML
        $html = '<div class="reading-time-box" style="'.$boxStyle.'">';
        $html .= '<span class="reading-time-icon" style="color:'.$iconColor.'">⏱</span>';
        $html .= '<div class="reading-time-text">';
        $html .= htmlspecialchars($prefixText);
        $html .= ' <span class="reading-time-minutes" style="font-size:'.$minutesSize.'px;color:'.$minutesColor.'">' . $readingTime . '</span> ';
        $html .= htmlspecialchars($suffixText);
        $html .= '</div>';
        
        // 显示详细信息
        if ($showDetails) {
            $html .= '<div class="reading-time-details" style="color:'.$detailColor.'">';
            $html .= '<span class="reading-time-words">' . $wordCount . ' 字</span>';
            $html .= '<span class="reading-time-speed">' . $readingSpeed . ' 字/分</span>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
        
        // 根据配置决定插入位置
        if ($displayPosition === 'before') {
            return $html . $content;
        } else {
            return $content . $html;
        }
    }

    /**
     * 清理文章内容
     * 
     * @access private
     * @param string $content 原始内容
     * @return string 清理后的内容
     */
    private static function cleanContent($content)
    {
        // 移除HTML标签
        $clean = strip_tags($content);
        
        // 移除短代码（如果有）
        $clean = preg_replace('/\[[^\]]+\]/', '', $clean);
        
        // 移除多余空格和换行
        $clean = preg_replace('/\s+/', ' ', $clean);
        
        return trim($clean);
    }
}