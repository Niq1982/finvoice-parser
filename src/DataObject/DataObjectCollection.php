<?php
namespace FinvoiceParser\DataObject;

use FinvoiceParser\DataObject\DataObject;

/**
 * A traversable and serializable collection of data objects
 */
abstract class DataObjectCollection implements \Iterator, \JsonSerializable
{
    protected array $items = [];

    private function __construct(array $items = [])
    {
        foreach ($items as $item) {
            $this->add($item);
        }
    }

    public function toArray()
    {
        return $this->items;
    }

    public function jsonSerialize(): array
    {
        return array_map(fn($item) => $item->jsonSerialize(), $this->items);
    }

    public static function create($items = []): static
    {
        return new static($items);
    }

    public function add(DataObject $item): void
    {
        $this->items[] = $item;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function empty(): bool
    {
        return empty($this->items);
    }

    public function first(): DataObject|null
    {
        return reset($this->items) ?? null;
    }

    public function key(): mixed
    {
        return key($this->items);
    }
    public function next(): void
    {
        next($this->items);

    }
    public function rewind(): void
    {
        reset($this->items);
    }
    public function valid(): bool
    {
        return key($this->items) !== null;
    }
    /**
     * Sort the collection in place using the provided sort function
     */
    public function sort(callable $sortFunction): void
    {
        usort($this->items, $sortFunction);
    }

    public function filter(callable $filterFunction): static
    {
        return static::create(array_filter($this->items, $filterFunction));
    }
}