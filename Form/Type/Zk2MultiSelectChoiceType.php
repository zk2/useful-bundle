<?php

namespace Zk2\UsefulBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * Zk2MultiSelectChoiceType
 * Implements the widget type "multiselect" in form template
 *
 */
class Zk2MultiSelectChoiceType extends Zk2MultiSelectAbscractType
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'zk2_useful_multiselect_choice_type';
    }
}