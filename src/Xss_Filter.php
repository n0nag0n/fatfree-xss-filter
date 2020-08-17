<?php

namespace n0nag0n;

/**
* Xss_Filter for Fat-Free
*
* NOTICE : copyfrom(Xss_Filter::filter('POST'));
*
* @author Dams & Tom & Rich
* @copyright Copyright (c) 2018
* @access public
 */

class Xss_Filter extends \Prefab
{

    /**
    * @var bool $allow_http_value
    * @access private
    */
    private $allow_http_value = true;

    /**
    * @var string $input
    * @access private
    */
    private $input;
    /**
    * @var array $preg_patterns
    * @access private
    */
    private $preg_patterns = array(
        // Fix &entity\n
        '!(&#0+[0-9]+)!' => '$1;',
        '/(&#*\w+)[\x00-\x20]+;/u' => '$1;>',
        '/(&#x*[0-9A-F]+);*/iu' => '$1;',
        //any attribute starting with "on" or xml name space
        '#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu' => '$1>',
        //javascript: and VB script: protocols
        '#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu' => '$1=$2nojavascript...',
        '#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu' => '$1=$2novbscript...',
        '#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u' => '$1=$2nomozbinding...',
        // Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
        '#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i' => '$1>',
        '#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu' => '$1>',
        // namespace elements
        '#</*\w+:\w[^>]*+>#i' => '',
        //unwanted tags
        '#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i' => ''
    );

    /**
     * @var array
     */
    private $normal_patterns = array(
        /*'\'' => '&apos;',*/
        '"' => '&quot;',
        '&' => '&amp;',
        '<' => '&lt;',
        '>' => '&gt;',
        //possible SQL injection remove from string with there is no '
        'SELECT * FROM' => '',
        'SELECT(' => '',
        'SLEEP(' => '',
        'AND (' => '',
        ' AND' => '',
        '(CASE' => ''
    );
    
    /*Rich & Tm & Dams*/
    private static $antiXss = null;
    private static function getAntiXss()
    {
        if (!self::$antiXss) {
            self::$antiXss = new Xss_Filter();
        }
        return self::$antiXss;
	}
	
	/**
	 * Filters a hive variable on attached to the main FatFree object
	 *
	 * @param string|array $hive_var
	 * @return string|array
	 */
    public static function filter($hive_var)
    {
        if (!is_array($hive_var)) {
            $orig_param = $hive_var;
            $hive_var = \Base::instance()->get($hive_var);
            if (!is_array($hive_var)) {
                $hive_var = [$orig_param =>$hive_var];
            }
        }
        $antiXss = self::getAntiXss();
        array_walk_recursive($hive_var, function (&$item, $key) use ($antiXss) {
            $item =$antiXss->filter_it($item);
        });
        return ($hive_var);
    }
	
	/**
	 * Filters a given variable
	 *
	 * @param mixed $scalar_var
	 * @return mixed
	 */
    public static function filterScalar($scalar_var)
    {
        if (!is_scalar($scalar_var)) {
            return self::filter($scalar_var);
        }
        return self::getAntiXss()->filter_it($scalar_var);
    }
    /**/

    /**
    * Xss_Filter::filter_it()
    *
    * @access public
    * @param string $input
    * @return string
    */
    public function filter_it($input)
    {
        $this->input = html_entity_decode($input, ENT_NOQUOTES, 'UTF-8');
        $this->normal_replace();
        $this->do_grep();
        return $this->input;
    }

    /**
    * Xss_Filter::allow_http()
    *
    * @access public
    */
    public function allow_http()
    {
        $this->allow_http_value = true;
    }

    /**
    * Xss_Filter::disallow_http()
    *
    * @access public
    */
    public function disallow_http()
    {
        $this->allow_http_value = false;
    }

    /**
    * Xss_Filter::remove_get_parameters()
    *
    * @access public
    * @param $url string
    * @return string
    */
    public function remove_get_parameters($url)
    {
        return preg_replace('/\?.*/', '', $url);
    }

    /**
    * Xss_Filter::normal_replace()
    *
    * @access private
    */
    private function normal_replace()
    {
        /*$this->input = str_replace(array('&amp;', '&lt;', '&gt;'), array('&amp;amp;', '&amp;lt;', '&amp;gt;'), $this->input);*/
        /*$this->input = str_replace(array('&lt;', '&gt;'), array('&amp;amp;', '&amp;lt;', '&amp;gt;'), $this->input);/*/
        if ($this->allow_http_value === false) {
            $this->input = str_replace(array('&', '%', 'script', 'http', 'localhost'), array('', '', '', '', ''), $this->input);
        } else {
            $this->input = str_replace(array('&', '%', 'script', 'localhost', '../'), array('', '', '', '', ''), $this->input);
        }
        foreach ($this->normal_patterns as $pattern => $replacement) {
            $this->input = str_replace($pattern, $replacement, $this->input);
        }
    }

    /**
    * Xss_Filter::do_grep()
    *
    * @access private
    */
    private function do_grep()
    {
        foreach ($this->preg_patterns as $pattern => $replacement) {
            $this->input = preg_replace($pattern, $replacement, $this->input);
        }
    }
}