<?php

namespace Zk2\UsefulBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * ZkFileTypeAbstract
 * Implements the widget type "file upload" in form template
 *
 */
abstract class ZkFileTypeAbstract extends AbstractType
{
    protected $widgetType;

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $defaults = array(
            'image_url' => null,
            'widget_type' => $this->widgetType,
            'widget_width' => '100px',
            'widget_height' => '100px',
            'select_label' => 'Select image',
            'change_label' => 'Change',
            'remove_label' => 'Remove',
        );
        $ajaxDefaults = array(
            'total_class' => null,
            'total_id' => null,
            'total_property' => null,
            'parent_class' => null,
            'parent_id' => null,
            'parent_property' => null,
        );
        $resolver->setDefaults(
            array('zkFileSettings' => $defaults, 'zkAjaxSettings' => $ajaxDefaults)
        );
        $resolver->setNormalizer(
            'zkFileSettings',
            function (Options $options, $configs) use ($defaults) {
                return array_merge($defaults, $configs);
            }
        );
        $resolver->setNormalizer(
            'zkAjaxSettings',
            function (Options $options, $configs) use ($ajaxDefaults) {
                return array_merge($ajaxDefaults, $configs);
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        $view->vars = array_replace(
            $view->vars,
            array(
                'zkFileSettings' => $options['zkFileSettings'],
                'zkAjaxSettings' => $options['zkAjaxSettings']
            )
        );
    }

    public function getParent()
    {
        return FileType::class;
    }

    public function getBlockPrefix()
    {
        return "zk2_useful_".$this->widgetType."_file_type";
    }
}