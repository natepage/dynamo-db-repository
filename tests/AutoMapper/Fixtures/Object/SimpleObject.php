<?php
declare(strict_types=1);

namespace NatePage\DynamoDbRepository\Tests\AutoMapper\Fixtures\Object;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

final class SimpleObject
{
    /**
     * @var \Doctrine\Common\Collections\Collection<\NatePage\DynamoDbRepository\Tests\AutoMapper\Fixtures\Object\ItemDto>
     */
    private Collection $subItems;

    public function __construct(
        public string $id,
        public string $name,
        public ?string $description = null,
        public ?array $tags = null,
        public ?\DateTimeInterface $createdAt = null,
        public ?MyEnum $enum = null,
        public ?int $count = null,
        public ?float $price = null,
        public ?bool $enabled = null,
    ) {
        $this->subItems = new ArrayCollection();
    }

    public function computeSomething(): void
    {

    }

    public function addSubItem(ItemDto $item): void
    {
        $this->subItems->add($item);
    }

    /**
     * @return \Doctrine\Common\Collections\Collection<\NatePage\DynamoDbRepository\Tests\AutoMapper\Fixtures\Object\ItemDto>
     */
    public function getSubItems(): Collection
    {
        return $this->subItems;
    }

    public function removeSubItem(ItemDto $item): void
    {
        $this->subItems->removeElement($item);
    }
}
