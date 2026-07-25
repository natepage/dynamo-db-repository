<?php
declare(strict_types=1);

namespace NatePage\DynamoDbRepository\Tests\AutoMapper\Fixtures\Object;

final class ItemDto
{
    public function __construct(
        private string $name,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): ItemDto
    {
        $this->name = $name;

        return $this;
    }
}
