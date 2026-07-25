<?php
declare(strict_types=1);

namespace NatePage\DynamoDbRepository\Tests\AutoMapper\Fixtures\Object;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

final class WithCollectionAddRemoveObject
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

    public function addItem(ItemDto $itemDto): void
    {
        $this->items->add($itemDto);
    }

    public function removeItem(ItemDto $itemDto): void
    {
        $this->items->removeElement($itemDto);
    }

    /**
     * @param \Doctrine\Common\Collections\Collection<\NatePage\DynamoDbRepository\Tests\AutoMapper\Fixtures\Object\ItemDto> $items
     */
    public function setItems(Collection $items): void
    {
        $this->items = $items;
    }
}
