<?php

namespace Zk2\UsefulBundle\Form\DataTransformer;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\InvalidConfigurationException;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

class EntityToIdTransformer implements DataTransformerInterface
{
    protected
        $em,
        $class,
        $unitOfWork;

    public function __construct(EntityManager $em, $class)
    {
        $this->em = $em;
        $this->unitOfWork = $this->em->getUnitOfWork();
        $this->class = $class;
    }

    public function transform($entity)
    {
        if (null === $entity || '' === $entity) {
            return 'null';
        }

        if (!is_object($entity)) {
            throw new UnexpectedTypeException($entity, 'object');
        }

        if (!$this->unitOfWork->isInIdentityMap($entity)) {
            throw new InvalidConfigurationException('Entities passed to the choice field must be managed');
        }

        return $entity->getId();
    }

    public function reverseTransform($id)
    {
        if ('' === $id || null === $id) {
            return null;
        }

        if (!is_numeric($id)) {
            throw new UnexpectedTypeException($id, 'numeric '.$id);
        }

        $entity = $this->em->getRepository($this->class)->findOneById($id);

        if ($entity === null) {
            throw new TransformationFailedException(sprintf('The entity with key "%s" could not be found', $id));
        }

        return $entity;
    }
}
