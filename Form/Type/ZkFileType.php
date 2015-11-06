<?php

namespace Zk2\UsefulBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * ZkFileType
 * Implements the widget type "file upload" in form template
 *
 */
class ZkFileType extends AbstractType
{
    protected $widget_type;

    /**
     * Constructor
     *
     * @param string $parent_type
     */
    public function __construct($widget_type)
    {
        $this->widget_type = $widget_type;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'image_url' => null,
                'as_ajax' => false,
                'widget_type' => $this->widget_type,
                'widget_width' => '100px',
                'widget_height' => '100px',
                'select_label' => 'Select image',
                'change_label' => 'Change',
                'remove_label' => 'Remove',
            )
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
                'image_url' => $options['image_url'],
                'as_ajax' => $options['as_ajax'],
                'widget_type' => $options['widget_type'],
                'widget_width' => $options['widget_width'],
                'widget_height' => $options['widget_height'],
                'select_label' => $options['select_label'],
                'change_label' => $options['change_label'],
                'remove_label' => $options['remove_label'],
            )
        );
    }


    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'file';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return "zk2_useful_".$this->widget_type."_file_type";
    }
}