<?php

namespace Zk2\UsefulBundle\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;

/**
 * Zk2MultiSelectEntityType
 * Implements the widget type "multiselect" in form template
 *
 */
class Zk2MultiSelectEntityType extends Zk2MultiSelectAbscractType
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return EntityType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'zk2_useful_multiselect_entity_type';
    }
}