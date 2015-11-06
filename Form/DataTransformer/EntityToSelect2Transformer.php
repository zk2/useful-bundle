<?php

namespace Zk2\UsefulBundle\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;


class EntityToSelect2Transformer implements DataTransformerInterface
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $unitOfWork;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em, $class)
    {
        $this->em = $em;
        $this->class = $class;
    }

    /**
     * Transforms an object (use) to a string (id).
     *
     * @param  array $array
     * @return ArrayCollection
     */
    public function transform($collection)
    {
        $newCollection = new ArrayCollection();

        if ($collection instanceof PersistentCollection) {
            foreach ($collection as $entity) {
                if (!is_object($entity)) {
                    return new ArrayCollection();
                }
                $newCollection[$entity->getId()] = $entity;
            }
        }

        return $newCollection;
    }

    /**
     * Transforms a string (id) to an object (item).
     *
     * @param  string $id
     * @return PersistentCollection|ArrayCollection
     */
    public function reverseTransform($array)
    {
        $collection = new ArrayCollection();

        foreach ($array as $value) {
            $entity = $this->em->getRepository($this->class)->find($value);

            if (null === $entity) {
                throw new TransformationFailedException('Entity by id '.$value.' not found');
            }

            $collection->add($entity);
        }

        return $collection;
    }
}
