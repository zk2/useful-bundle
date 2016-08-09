<?php
namespace Zk2\UsefulBundle\Twig\Extension;

use Twig_Extension;

/**
 * UsefulExtension Twig Extension
 *
 * Class that extends the Twig_Extension
 */
class UsefulExtension extends Twig_Extension
{
    /**
     * @return array
     * @see \Twig_Extension
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('wordPreview', 'renderWordPreview'),
            new \Twig_SimpleFunction('textPreview', 'renderTextPreview'),
            new \Twig_SimpleFunction('zkPlural', 'renderZkPlural'),
            new \Twig_SimpleFunction('zkSelect', 'renderZkSelect'),
        );
    }

    /**
     *  Renders Word Preview
     *
     * @param string $text
     * @param integer $countSymbols
     * @return string or false
     */
    public function renderWordPreview($text, $countSymbols = 10)
    {
        if (strlen($text) > $countSymbols) {
            $text = substr($text, 0, $countSymbols) . '...';
        }

        return $text;
    }

    /**
     *  Renders Text Preview
     *
     * @param string $text
     * @param integer $countWords
     * @return string
     */
    public function renderTextPreview($text, $countWords = 20)
    {
        $text = strip_tags($text, '<a><img>');
        $words = explode(' ', $text);
        $newText = '';
        $count = 0;
        $isBig = false;

        foreach ($words as $word) {
            if ($word) {
                if ($count <= $countWords) {
                    $newText .= ' ' . $word;
                    $count++;
                } else {
                    $newText .= '...';
                    $isBig = true;
                    break;
                }
            }
        }

        return $isBig ? $newText : $text;
    }


    /**
     *  ZkPlural
     *
     * @param integer $int
     * @param array $variants
     * @return string
     */
    public function renderZkPlural($int, array $variants)
    {
        $key = (($int % 10 == 1) and ($int % 100 != 11))
            ? 0
            : ((($int % 10 >= 2) and ($int % 10 <= 4) and (($int % 100 < 10) or ($int % 100 >= 20)))
                ? 1
                : 2
            );

        return $variants[$key];
    }

    /**
     *  ZkSelect
     *
     * @param integer $key
     * @param array $variants
     * @return string
     */
    public function renderZkSelect($key, array $variants)
    {
        return isset($variants[$key]) ? $variants[$key] : null;
    }

###########################################################################################3

    /**
     * @return array
     * @see \Twig_Extension
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('strExplode', array($this, 'strExplodeFilter')),
            new \Twig_SimpleFilter('int_to_time', array($this, 'intToTimeFilter')),
            new \Twig_SimpleFilter('int_to_date', array($this, 'intToDateFilter')),
        );
    }

    /**
     *  strExplodeFilter
     *
     * @param array $array
     *
     * @return string
     */
    public function strExplodeFilter($array)
    {
        $str = "";
        foreach ($array as $value) {
            $i = 0;
            foreach ($value as $key => $val) {
                $str .= str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $i) . $val . '<br>';
                $i++;
            }
        }

        return $str;
    }

    /**
     *  intToTimeFilter
     *
     * @param integer $int
     *
     * @return string
     */
    public function intToTimeFilter($int)
    {
        $date = new \DateTime(date('Y-m-d 00:00:00'));
        $date->modify($int . ' second');

        return $date->format('H:i');
    }

    /**
     *  intToDateFilter
     *
     * @param integer $int
     *
     * @return string
     */
    public function intToDateFilter($int)
    {
        return date('Y-m-d H:i', $int);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'zk2.useful.twig_extension';
    }
}