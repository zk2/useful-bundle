<?php

namespace Zk2\Bundle\UsefulBundle\Form\Type;

use Zk2\Bundle\UsefulBundle\Form\DataTransformer\EntityToSelect2Transformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\Exception\InvalidConfigurationException;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\Common\Persistence\ObjectManager;

class Select2MultipleEntityType extends AbstractType
{
    private $om;

    public function __construct( ObjectManager $om )
    {
        $this->om = $om;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->resetViewTransformers();
        $builder->addViewTransformer(new EntityToSelect2Transformer(
            $this->om,
            $options['class']
        ), true);
        
        $builder->setAttribute('em_name',            $options['em_name']);
        $builder->setAttribute('class',              $options['class']);
        $builder->setAttribute('property',           $options['property']);
        $builder->setAttribute('condition_operator', $options['condition_operator']);
        $builder->setAttribute('max_rows',           $options['max_rows']);
        $builder->setAttribute('options',            $options['options']);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['class'] =              $form->getConfig()->getAttribute('class');
        $view->vars['property'] =           $form->getConfig()->getAttribute('property');
        $view->vars['condition_operator'] = $form->getConfig()->getAttribute('condition_operator');
        $view->vars['em_name'] =            $form->getConfig()->getAttribute('em_name');
        $view->vars['max_rows'] =           $form->getConfig()->getAttribute('max_rows');
        $view->vars['options'] =            $form->getConfig()->getAttribute('options');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'multiple'           => true,
            'class'              => null,
            'property'           => 'name',
            'condition_operator' => 'begins_with',
            'em_name'            => 'default',
            'max_rows'           => 100,
            'options'            => array()
        ));
    }

    public function getName()
    {
        return 'zk2_useful_select2_multiple_entity';
    }

    public function getParent()
    {
        return 'choice';
    }

}