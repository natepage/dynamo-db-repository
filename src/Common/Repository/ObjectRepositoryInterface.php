<?php
declare(strict_types=1);

namespace NatePage\DynamoDbRepository\Common\Repository;

/**
 * @template-covariant T of object
 */
interface ObjectRepositoryInterface
{
    public ?string $lastEvaluatedKey = null {
        get;
    }

    /**
     * @phpstan-return class-string<T>
     */
    public static function getObjectClass(): string;

    /**
     * @phpstan-param T $object
     *
     * @phpstan-return T
     */
    public function delete(object $object): object;

    /**
     * @phpstan-return T|null
     */
    public function find(string $id): ?object;

    /**
     * @phpstan-return iterable<T>
     */
    public function findAll(): iterable;

    /**
     * @phpstan-param T $object
     *
     * @phpstan-return T
     */
    public function save(object $object): object;

    /**
     * @phpstan-param T $object
     *
     * @phpstan-return T
     */
    public function update(object $object): object;
}
