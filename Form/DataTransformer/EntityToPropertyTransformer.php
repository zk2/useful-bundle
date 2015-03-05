<?php

namespace Zk2\Bundle\UsefulBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Form\Exception\InvalidConfigurationException;
use Doctrine\Common\Persistence\ObjectManager;

class EntityToPropertyTransformer implements DataTransformerInterface
{
    protected
        $om,
        $class,
        $property,
        $unitOfWork
    ;

    public function __construct(ObjectManager $om, $class, $property)
    {
        $this->om = $om;
        $this->unitOfWork = $this->om->getUnitOfWork();
        $this->class = $class;
        $this->property = $property;
    }

    public function transform($entity)
    {
        if (null === $entity) return null;

        if (!$this->unitOfWork->isInIdentityMap($entity)) {
            throw new InvalidConfigurationException('Entities passed to the choice field must be managed');
        }

        if ($this->property) {
            $propertyAccessor = PropertyAccess::getPropertyAccessor();
            return $propertyAccessor->getValue($entity, $this->property);
        }

        return current($this->unitOfWork->getEntityIdentifier($entity));
    }


    public function reverseTransform($prop_value)
    {
        if (!$prop_value) return null;

        $entity = $this->om->getRepository($this->class)->findOneBy(array($this->property => $prop_value));

        return $entity;
    }
}
