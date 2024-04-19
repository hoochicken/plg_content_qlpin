<?php
/**
 * @package        plg_qlpin
 * @copyright    Copyright (C) 2024 ql.de All rights reserved.
 * @author        Mareike Riegel mareike.riegel@ql.de
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

//no direct access
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;

defined('_JEXEC') or die ('Restricted Access');

jimport('joomla.plugin.plugin');

class plgContentQlpin extends CMSPlugin
{

    private $str_call_start = 'qlpin';
    private $notag = '{noQlpin}';
    protected $arr_attributes = array('id', 'class', 'style', 'layout',);
    protected $idDefault = '';
    protected $classDefault = '';
    protected $styleDefault = '';
    protected $layoutDefault = '';
    protected $states = array();


    function __construct(&$subject, $config) {
        $lang = Factory::getApplication()->getLanguage();
        $lang->load('plg_content_qlmodule', dirname(__FILE__));
        parent::__construct($subject, $config);
    }

    /**
     * onContentPrepare :: some kind of controller of plugin
     */
    public function onContentPrepare($context, &$article, &$params, $page = 0)
    {
        if ($context == 'com_finder.indexer') return true;

        $input = Factory::getApplication()->input;
        $option = $input->getString('option') . ':' . $input->getString('view');

        if (1 != $this->params->get('withinarticle') and false == strpos($article->text, '{' . $this->str_call_start)) return true;

        if (true == strpos($article->text, $this->notag)) {
            $article->text = str_replace($this->notag, '', $article->text);
            return true;
        }
        if (!isset($article->catid) or !in_array($article->catid, $this->params->get('category')) or !is_array($this->params->get('option'))) return true;

        if (!in_array($option, $this->params->get('option'))) return true;
        $html = $this->getHtml($article->id);
        if (1 == $this->params->get('withinarticle')) {
            if ('bottom' == $this->params->get('vertical')) $article->text .= $html;
            else $article->text = $html . $article->text;
        }
        $article->text = $this->replaceStartTags($article->text, $article->id);
        $this->getStyles();
    }

    /*
     * method to get attributes
     */
    function replaceStartTags($text, $id)
    {
        $matches = $this->getMatches($text, $this->str_call_start);
        if (count($matches) > 0) {
            foreach ($matches as $match) {
                $arr_attributes = $this->getAttributes($match[1]);
                if (is_array($arr_attributes)) foreach ($arr_attributes as $k => $v) if ('' != $v) $this->states[$k] = $v;
                $text = preg_replace("|$match[0]|", addcslashes($this->getHtml($id), '\\$'), $text, 1);
            }
        }
        return $text;
    }

    /*
    * method to get html source code of gallery
    */
    function getHtml($articleId)
    {
        $states = $this->states;
        $params = $this->params;
        ob_start();
        include $this->getLayoutPath($states['layout']);
        $strHtml = ob_get_contents();
        ob_end_clean();
        return $strHtml;
    }

    function getLayoutPath($layout)
    {
        $tplName = Factory::getApplication()->getTemplate('template')->template;
        $path = JPATH_BASE . '/templates/' . $tplName . '/html/plg_content_qlpin';
        $pathOverride = $path . '/default.php';
        $pathAlternative = $path . '/' . $layout . '.php';
        if (file_exists($pathAlternative)) return $pathAlternative;
        if (file_exists($pathOverride)) return $pathOverride;
        return JPATH_BASE . '/plugins/content/' . $this->get('_name') . '/html/default.php';
    }

    /*
     * method to get attributes
     */
    function getAttributes($string)
    {
        $selector = implode('|', $this->arr_attributes);
        preg_match_all('~(' . $selector . ')="(.+?)"~s', $string, $matches);
        $arr_attributes = array();
        if (is_array($matches)) foreach ($matches[0] as $k => $v) if (isset($matches[1][$k]) and isset($matches[2][$k])) $arr_attributes[$matches[1][$k]] = $matches[2][$k];
        return $arr_attributes;
    }

    /**
     * method to clear tags
     */
    function clearTags($str)
    {
        $str = str_replace('<p>{' . $this->str_call_start, '{' . $this->str_call_start, $str);
        $regex = '!{' . $this->str_call_start . '\s(.*?)}</p>!';
        preg_match_all($regex, $str, $matches, PREG_SET_ORDER);
        //echo '<pre>';print_r($matches);die;
        if (0 < count($matches)) foreach ($matches as $k => $v) {
            $str = preg_replace('?' . $v[0] . '?', '{' . $this->str_call_start . strip_tags($v[1]) . '}', $str);
            //$str=preg_replace('?'.$v[0].'?','{'.$this->str_call_start.$v[1].'}',$str);
        }
        return $str;
    }

    /**
     * method to get matches according to search string
     * @param $text string haystack
     * @param $searchString string needle, string to be searched
     */
    public function getMatches($text, $searchString)
    {
        $searchString = preg_replace('!{\?}!', ' ', $searchString);
        $searchString = preg_replace('?/?', '\/', $searchString);
        $regex = '/{' . $searchString . '+(.*?)}/i';
        preg_match_all($regex, $text, $matches, PREG_SET_ORDER);
        return $matches;
    }

    /*
    * method to get thml source code of gallery
    */
    function getStyles()
    {
        $styles = array();
        $styles[] = '.qlpin';
        $styles[] = '{';
        $styles[] = 'border:' . $this->params->get('borderWidth') . 'px ' . $this->params->get('borderStyle') . ' ' . $this->params->get('borderColor') . ';';
        $styles[] = 'border-radius:' . $this->params->get('borderRadius') . 'px;';
        $styles[] = 'background:' . $this->params->get('background') . ';';
        $styles[] = 'color:' . $this->params->get('color') . ';';
        $styles[] = 'padding:' . $this->params->get('padding') . 'px;';
        $styles[] = '}';
        $styles[] = '.qlpin h3';
        $styles[] = '{';
        $styles[] = 'margin-top:0';
        $styles[] = '}';
        $styles[] = '.row-fluid .qlpin[class*="span"]';
        $styles[] = '{';
        $styles[] = 'margin:0;';
        $styles[] = 'float:' . $this->params->get('horizontal') . ';';
        if ('left' == $this->params->get('horizontal', 'left')) $styles[] = 'margin-right:' . $this->params->get('padding') . 'px;';
        else $styles[] = 'margin-left:' . $this->params->get('padding') . 'px;';
        if ('top' == $this->params->get('vertical', 'top')) $styles[] = 'margin-bottom:' . $this->params->get('padding') . 'px;';
        else $styles[] = 'margin-top:' . $this->params->get('padding') . 'px;';
        $styles[] = '}';
        Factory::getDocument()->addStyleDeclaration(implode("\n", $styles));
    }

    function parsePhpViaFile($custom)
    {
        $tmpfname = tempnam(JPATH_SITE . '/tmp', 'html');
        $handle = fopen($tmpfname, 'w');
        //$custom='defined(\'_JEXEC\') or die (\'Restricted Access\');'.$custom;
        fwrite($handle, $custom, strlen($custom));
        fclose($handle);
        include_once($tmpfname);
        unlink($tmpfname);
    }

    /*
    * method to set states
    */
    function setStates()
    {
        $this->states = array();
        foreach ($this->arr_attributes as $k => $v) {
            if (isset($this->{$v . 'Default'})) $this->states[$v] = $this->params->get($v, $this->{$v . 'Default'});
            else $this->params->get($v);
        }
    }

}