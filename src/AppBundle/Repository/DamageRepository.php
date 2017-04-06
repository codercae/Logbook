<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * DamageRepository
 *
 * This class was generated by the PhpStorm "Php Annotations" Plugin. Add your own custom
 * repository methods below.
 */
class DamageRepository extends EntityRepository
{
    public function findLast()
    {
        return $this->getEntityManager()
            ->createQuery('id')
            ->setMaxResults(1)
            ->getResult();
    }
}