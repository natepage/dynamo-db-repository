<?php
declare(strict_types=1);

namespace NatePage\DynamoDbRepository\Tests\AutoMapper\Fixtures\Object;

final readonly class SimpleObject
{
    public function __construct(
        public string $id,
        public string $name,
        public ?string $description = null,
        public ?array $tags = null
    ) {
    }

    public function computeSomething(): void
    {

    }
}
