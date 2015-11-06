<?php

namespace Zk2\UsefulBundle\Form\DataTransformer;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\InvalidConfigurationException;
use Symfony\Component\PropertyAccess\PropertyAccess;

class EntityToPropertyTransformer implements DataTransformerInterface
{
    protected
        $om,
        $class,
        $property,
        $unitOfWork;

    public function __construct(ObjectManager $om, $class, $property)
    {
        $this->om = $om;
        $this->unitOfWork = $this->om->getUnitOfWork();
        $this->class = $class;
        $this->property = $property;
    }

    public function transform($entity)
    {
        if (null === $entity) {
            return null;
        }

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
        if (!$prop_value) {
            return null;
        }

        $entity = $this->om->getRepository($this->class)->findOneBy(array($this->property => $prop_value));

        return $entity;
    }
}
