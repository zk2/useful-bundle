<?php

namespace Zk2\UsefulBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Zk2MultiSelectAbscractType
 * Implements the widget type "multiselect" in form template
 *
 */
abstract class Zk2MultiSelectAbscractType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'expanded' => false,
            'multiple' => true,
            'ZkHeight' => 200,
            'ZkWidth' => 200,
            'ZkSearch' => 1,
            'ZkRange' => 0,
            'ZkDescrRout' => 0,
            'ZkOptionsDisabled' => '[]',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        $view->vars = array_replace(
            $view->vars,
            [
                'ZkHeight' => $options['ZkHeight'],
                'ZkWidth' => $options['ZkWidth'],
                'ZkSearch' => $options['ZkSearch'],
                'ZkRange' => $options['ZkRange'],
                'ZkDescrRout' => $options['ZkDescrRout'],
                'ZkOptionsDisabled' => $options['ZkOptionsDisabled'],
            ]
        );
    }
}