<?php
declare(strict_types=1);

namespace NatePage\DynamoDbRepository\Doctrine\Repository;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository as BaseEntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use NatePage\DynamoDbRepository\Common\Repository\ObjectRepositoryInterface;

final class EntityRepository extends BaseEntityRepository
{
    public function __construct(
        private readonly ObjectRepositoryInterface $repository,
        EntityManagerInterface $em,
        ClassMetadata $class
    ) {
        parent::__construct($em, $class);
    }

    public function find(mixed $id, int|LockMode|null $lockMode = null, ?int $lockVersion = null): object|null
    {
        if (\is_string($id) === false) {
            return null;
        }

        $entity = $this->repository->find($id);
        if ($entity != null) {
            $this->getEntityManager()->getUnitOfWork()->registerManaged($entity, $id, []);
        }

        return $entity;
    }
}
