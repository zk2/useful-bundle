<?php
namespace Zk2\Bundle\UsefulBundle\Twig\Extension;

use Twig_Extension;
use Twig_Filter_Method;
use Twig_Function_Method;
use Twig_Filter_Function;

/**
 * UsefulExtension Twig Extension
 * 
 * Class that extends the Twig_Extension
 */
class UsefulExtension extends Twig_Extension
{
    /**
     *  @return array
     *  @see \Twig_Extension
     */
    public function getFunctions()
    {
        return array(
            'wordPreview'    => new \Twig_Function_Method($this, 'renderWordPreview', array('is_safe' => array('html'))),
            'textPreview'    => new \Twig_Function_Method($this, 'renderTextPreview', array('is_safe' => array('html'))),
            'zkMatches'      => new \Twig_Function_Method($this, 'renderMatches', array('is_safe' => array('html'))),
            'zkPregMatch'    => new \Twig_Function_Method($this, 'renderZkPregMatch', array('is_safe' => array('html'))),
            'zkTranschoice'  => new \Twig_Function_Method($this, 'renderZkTranschoice', array('is_safe' => array('html'))),
        );
    }

    /**
     *  Renders Word Preview
     *
     *  @param string $text
     *  @param integer $count_synbols
     *  @return string or false
     */
    public function renderWordPreview($text, $count_synbols=10)
    {
        if(strlen($text) > $count_synbols)
        {
            $text = substr ($text, 0, $count_synbols).'...';
        }
        return $text;
    }
    
    /**
     *  Renders Text Preview
     *
     *  @param string $text
     *  @param integer $count_words
     *  @return string or false
     */
    public function renderTextPreview($text, $count_words=20)
    {
        $text = strip_tags($text,'<a><img>');
        $words = explode(' ', $text);
        $text = '';
        $cou = 0;
        $is_big = false;
        
        foreach($words as $word)
        {
            if($word)
            {
                if($cou <= $count_words)
                {
                    $text .= ' '.$word;
                    $cou++;
                }
                else
                {
                    $text .= '...';
                    $is_big = true;
                    break;
                }
            }
        }
        return $is_big ? $text : false;
    }
    
    /**
     *  Twig preg_match
     *
     *  @param $item
     *  @param $pattern
     *  @return boolean
     */
    public function renderMatches($pattern,$item)
    {
        return strpos($item,$pattern) !== false;
        //return preg_match($pattern,$item);
    }    
    
    /**
     *  ZkPregMatch
     *
     *  @param string $pattern
     *  @param string $subject
     *  @return boolean
     */
    public function renderZkPregMatch($pattern, $subject)
    {
        return preg_match($pattern, $subject);
    }
    
    
    /**
     *  ZkTranschoice
     *
     *  @param integer $int
     *  @param array $variantes
     *  @return string
     */
    public function renderZkTranschoice($int, array $variantes)
    {
        $key = (($int % 10 == 1) and ($int % 100 != 11)) ? 0
            : ((($int % 10 >= 2) and ($int % 10 <= 4) and
            (($int % 100 < 10) or ($int % 100 >= 20))) ? 1 : 2);
        return $variantes[$key];
    }

###########################################################################################3
    
    /**
     *  @return array
     *  @see \Twig_Extension
     */
    public function getFilters()
    {
        return array(
            'newStr'         => new \Twig_Filter_Method($this, 'newStrFilter'),
            'strExplode'     => new \Twig_Filter_Method($this, 'strExplodeFilter'),
            'print_r'        => new \Twig_Filter_Method($this, 'printRFilter'),
            'in_string'      => new \Twig_Filter_Method($this, 'inStringFilter'),
            'int_to_time'    => new \Twig_Filter_Method($this, 'intToTimeFilter'),
            'int_to_date'    => new \Twig_Filter_Method($this, 'intToDateFilter'),
        );
    }
    
    /**
     *  newStrFilter
     *
     *  @param string $str
     *
     *  @return str_replace(', ','<br>',$str)
     */
    public function newStrFilter($str)
    {         
        return str_replace(', ','<br>',$str);
    }
    
    /**
     *  strExplodeFilter
     *
     *  @param array $array
     *
     *  @return string
     */
    public function strExplodeFilter($array)
    {
        $str = "";
        foreach($array as $value)
        {
            $i = 0;
            foreach($value as $key => $val)
            {
                $str .= str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;',$i).$val.'<br>';
                $i++;
            }
        }
        return $str;
    }

    /**
     *  printRFilter
     *
     *  @param string $arr
     *
     *  @return print_r($arr)
     */
    public function printRFilter($arr)
    {         
        return print_r($arr);
    }
    
    /**
     *  inStringFilter
     *
     *  @param string $text
     *
     *  @return $text в одну строку
     */
    public function inStringFilter($text)
    {
        $text = preg_replace('|\s+|', ' ', $text);
        return $text;
    }

    /**
     *  intToTimeFilter
     *
     *  @param integer $int
     *
     *  @return string
     */
    public function intToTimeFilter($int)
    {         
        $date = new \DateTime(date('Y-m-d 00:00:00'));
        $date->modify($int.' second');
        return $date->format('H:i');
    }

    /**
     *  intToDateFilter
     *
     *  @param integer $int
     *
     *  @return string
     */
    public function intToDateFilter($int)
    {         
        return date('Y-m-d H:i',$int);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'zk2.useful.twig_extension';
    }
}