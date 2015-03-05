<?php

namespace Zk2\Bundle\UsefulBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\Exception\FormException;
use Zk2\Bundle\UsefulBundle\Form\DataTransformer\EntityToPropertyTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\Common\Persistence\ObjectManager;

class EntityAjaxAutocompleteType extends AbstractType
{
    private $om;

    public function __construct( ObjectManager $om )
    {
        $this->om = $om;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (null === $options['class']) {
            throw new FormException('Option "class" is empty');
        }

        if (null === $options['property']) {
            throw new FormException('Option "property" is empty');
        }

        $builder->addViewTransformer(new EntityToPropertyTransformer(
            $this->om,
            $options['class'],
            $options['property']
        ), true);
        
        $query = $options['query'];
        
        if ($query instanceof \Closure)
        {
            $queryBuilder = $query($this->om->getRepository($options['class']));
            $query = $queryBuilder->getQuery()->getDql();
        }

        $builder->setAttribute('em_name',            $options['em_name']);
        $builder->setAttribute('class',              $options['class']);
        $builder->setAttribute('property',           $options['property']);
        $builder->setAttribute('query',              $query);
        $builder->setAttribute('condition_operator', $options['condition_operator']);
        $builder->setAttribute('max_rows',           $options['max_rows']);
        $builder->setAttribute('options',            $options['options']);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['class'] =              $form->getConfig()->getAttribute('class');
        $view->vars['property'] =           $form->getConfig()->getAttribute('property');
        $view->vars['condition_operator'] = $form->getConfig()->getAttribute('condition_operator');
        $view->vars['query'] =              $form->getConfig()->getAttribute('query');
        $view->vars['em_name'] =            $form->getConfig()->getAttribute('em_name');
        $view->vars['max_rows'] =           $form->getConfig()->getAttribute('max_rows');
        $view->vars['options'] =            $form->getConfig()->getAttribute('options');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'class'              => null,
            'property'           => null,
            'condition_operator' => 'begins_with',
            'em_name'            => 'default',
            'query'              => null,
            'max_rows'           => 100,
            'options'            => array()
        ));
    }

    public function getName()
    {
        return 'zk2_useful_entity_ajax_autocomplete';
    }

    public function getParent()
    {
        return 'text';
    }
}
