<?php
declare(strict_types=1);

namespace NatePage\DynamoDbRepository\Tests\AutoMapper\Fixtures\Object;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

final class WithCollectionObject
{
    /**
     * @var \Doctrine\Common\Collections\Collection<\NatePage\DynamoDbRepository\Tests\AutoMapper\Fixtures\Object\ItemDto>
     */
    private Collection $items;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    public function getItems(): Collection
    {
        return $this->items;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection<\NatePage\DynamoDbRepository\Tests\AutoMapper\Fixtures\Object\ItemDto> $items
     */
    public function setItems(Collection $items): void
    {
        $this->items = $items;
    }
}
