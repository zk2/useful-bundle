<?php

namespace Zk2\UsefulBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * ZkFileBaseType
 * Implements the widget type "file upload" in form template
 *
 */
class ZkFileBaseType extends ZkFileTypeAbstract
{
    protected $widgetType = 'base';
}