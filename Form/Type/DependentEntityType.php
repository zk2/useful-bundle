<?php

namespace Zk2\UsefulBundle\Form\Type;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\InvalidConfigurationException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zk2\UsefulBundle\Form\DataTransformer\EntityToIdTransformer;

class DependentEntityType extends AbstractType
{
    private $om;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (null === $options['class']) {
            throw new InvalidConfigurationException('Option "class" is empty');
        }

        if (null === $options['parent_field']) {
            throw new InvalidConfigurationException('Option "parent_field" is empty');
        }

        if (null === $options['property']) {
            throw new InvalidConfigurationException('Option "property" is empty');
        }

        $builder->addViewTransformer(
            new EntityToIdTransformer(
                $this->om,
                $options['class']
            ),
            true
        );

        $query = $options['query'];

        if ($query instanceof \Closure) {
            $queryBuilder = $query($this->om->getRepository($options['class']));
            $query = $queryBuilder->getQuery()->getDql();
        }

        $builder->setAttribute('class', $options['class']);
        $builder->setAttribute("parent_field", $options['parent_field']);
        $builder->setAttribute("no_result_msg", $options['no_result_msg']);
        $builder->setAttribute("empty_value", $options['empty_value']);
        $builder->setAttribute("property", $options['property']);
        $builder->setAttribute("em_name", $options['em_name']);
        $builder->setAttribute('query', $query);
        $builder->setAttribute("order_direction", $options['order_direction']);
        $builder->setAttribute("order_property", $options['order_property']);

    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['class'] = $form->getConfig()->getAttribute('class');
        $view->vars['parent_field'] = $form->getConfig()->getAttribute('parent_field');
        $view->vars['no_result_msg'] = $form->getConfig()->getAttribute('no_result_msg');
        $view->vars['empty_value'] = $form->getConfig()->getAttribute('empty_value');
        $view->vars['property'] = $form->getConfig()->getAttribute('property');
        $view->vars['em_name'] = $form->getConfig()->getAttribute('em_name');
        $view->vars['query'] = $form->getConfig()->getAttribute('query');
        $view->vars['order_direction'] = $form->getConfig()->getAttribute('order_direction');
        $view->vars['order_property'] = $form->getConfig()->getAttribute('order_property');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'class' => null,
                'empty_value' => '',
                'parent_field' => null,
                'property' => null,
                'compound' => false,
                'em_name' => 'default',
                'query' => null,
                'no_result_msg' => 'No result',
                'order_direction' => 'ASC',
                'order_property' => 'id',
            )
        );
    }

    public function getParent()
    {
        return 'form';
    }

    public function getName()
    {
        return 'zk2_useful_dependent_entity';
    }
}
