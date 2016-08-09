<?php

namespace Zk2\UsefulBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\InvalidConfigurationException;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zk2\UsefulBundle\Form\DataTransformer\EntityToPropertyTransformer;

class EntityAjaxAutocompleteType extends AbstractType
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (null === $options['class']) {
            throw new InvalidConfigurationException('Option "class" is empty');
        }

        if (null === $options['property']) {
            throw new InvalidConfigurationException('Option "property" is empty');
        }

        $builder->addViewTransformer(
            new EntityToPropertyTransformer(
                $this->em,
                $options['class'],
                $options['property']
            ),
            true
        );

        $query = $options['query'];

        if ($query instanceof \Closure) {
            $queryBuilder = $query($this->em->getRepository($options['class']));
            $query = $queryBuilder->getQuery()->getDql();
        }

        $builder->setAttribute('em_name', $options['em_name']);
        $builder->setAttribute('class', $options['class']);
        $builder->setAttribute('property', $options['property']);
        $builder->setAttribute('query', $query);
        $builder->setAttribute('condition_operator', $options['condition_operator']);
        $builder->setAttribute('max_rows', $options['max_rows']);
        $builder->setAttribute('options', $options['options']);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['class'] = $form->getConfig()->getAttribute('class');
        $view->vars['property'] = $form->getConfig()->getAttribute('property');
        $view->vars['condition_operator'] = $form->getConfig()->getAttribute('condition_operator');
        $view->vars['query'] = $form->getConfig()->getAttribute('query');
        $view->vars['em_name'] = $form->getConfig()->getAttribute('em_name');
        $view->vars['max_rows'] = $form->getConfig()->getAttribute('max_rows');
        $view->vars['options'] = $form->getConfig()->getAttribute('options');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'class' => null,
                'property' => null,
                'condition_operator' => 'begins_with',
                'em_name' => 'default',
                'query' => null,
                'max_rows' => 100,
                'options' => array(),
            )
        );
    }

    public function getBlockPrefix()
    {
        return 'zk2_useful_entity_ajax_autocomplete';
    }

    public function getParent()
    {
        return TextType::class;
    }
}
