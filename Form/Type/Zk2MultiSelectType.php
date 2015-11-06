<?php

namespace Zk2\UsefulBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Zk2MultiSelectType
 * Implements the widget type "multiselect" in form template
 *
 */
class Zk2MultiSelectType extends AbstractType
{
    protected $parent_type;

    /**
     * Constructor
     *
     * @param string $parent_type
     */
    public function __construct($parent_type)
    {
        $this->parent_type = $parent_type;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'ZkHeight' => 200,
                'ZkWidth' => 200,
                'ZkSearch' => true,
                'ZkRange' => false,
                'ZkDescr' => false,
                'ZkOptionsDisabled' => '[]',
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
                'ZkHeight' => $options['ZkHeight'],
                'ZkWidth' => $options['ZkWidth'],
                'ZkSearch' => $options['ZkSearch'],
                'ZkRange' => $options['ZkRange'],
                'ZkDescr' => $options['ZkDescr'],
                'ZkOptionsDisabled' => $options['ZkOptionsDisabled'],
            )
        );
    }


    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return $this->parent_type;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return sprintf("zk2_useful_multiselect_%s_type", $this->parent_type);
    }
}