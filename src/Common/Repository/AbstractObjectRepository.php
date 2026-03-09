<?php
declare(strict_types=1);

namespace NatePage\DynamoDbRepository\Common\Repository;

use AsyncAws\DynamoDb\DynamoDbClient;
use AsyncAws\DynamoDb\Exception\ConditionalCheckFailedException;
use AsyncAws\DynamoDb\Input\DeleteItemInput;
use AsyncAws\DynamoDb\Input\GetItemInput;
use AsyncAws\DynamoDb\Input\PutItemInput;
use AsyncAws\DynamoDb\Input\QueryInput;
use AsyncAws\DynamoDb\Input\ScanInput;
use AsyncAws\DynamoDb\Result\QueryOutput;
use AsyncAws\DynamoDb\Result\ScanOutput;
use AsyncAws\DynamoDb\ValueObject\AttributeValue;
use NatePage\DynamoDbRepository\Common\Exception\RepositoryNotConfiguredException;
use NatePage\DynamoDbRepository\Common\Naming\TableNamingStrategyInterface;
use NatePage\DynamoDbRepository\Common\Transformer\ItemObjectTransformerInterface;
use NatePage\Utils\Helper\StringHelper;

abstract class AbstractObjectRepository implements ObjectRepositoryInterface
{
    public ?string $lastEvaluatedKey = null {
        get => $this->lastEvaluatedKey;
    }

    private ?ItemObjectTransformerInterface $itemObjectTransformer = null;

    public function __construct(
        protected DynamoDbClient $dynamoDbClient,
        protected TableNamingStrategyInterface $tableNamingStrategy,
        private ?string $tableName = null,
        private readonly ?string $tablePrefix = null
    ) {
    }

    public function delete(object $object): object
    {
        $this->doDelete(static::getPrimaryKeyName(), $this->getPrimaryKeyValue($object));

        return $object;
    }

    public function find(string $id): ?object
    {
        return $this->doFind(static::getPrimaryKeyName(), $id);
    }

    public function findAll(): iterable
    {
        $result = $this->resolveLastEvaluateKey($this->dynamoDbClient->scan(new ScanInput([
            'TableName' => $this->getTableName(),
        ])));

        foreach ($result->getItems() as $item) {
            yield $this->toObject($item);
        }
    }

    public function save(object $object): object
    {
        $this->doOptimisticPutWithRetries(
            $object,
            static::getPrimaryKeyName(),
            $this->getOptimisticPutObjectReset(),
            $this->getOptimisticPutMaxAttempts()
        );

        return $object;
    }

    public function update(object $object): object
    {
        $this->doPutItem($object);

        return $object;
    }

    public function setItemObjectTransformer(ItemObjectTransformerInterface $itemObjectTransformer): void
    {
        $this->itemObjectTransformer = $itemObjectTransformer;
    }

    abstract public static function getObjectClass(): string;

    abstract public static function getPrimaryKeyName(): string;

    abstract protected function getPrimaryKeyValue(object $object): string;

    protected function decodeLastEvaluatedKey(?string $lastEvaluatedKey): ?array
    {
        if (StringHelper::isEmpty($lastEvaluatedKey)) {
            return null;
        }

        $lastEvaluatedKey = StringHelper::urlSafeBase64Decode($lastEvaluatedKey);
        $lastEvaluatedKey = \json_validate($lastEvaluatedKey) ? \json_decode($lastEvaluatedKey, true) : null;

        return \is_array($lastEvaluatedKey)
            ? \array_map(static fn ($v) => AttributeValue::create($v), $lastEvaluatedKey)
            : null;
    }

    protected function doDelete(string $keyName, string $keyValue): void
    {
        $result = $this->dynamoDbClient->deleteItem(new DeleteItemInput([
            'TableName' => $this->getTableName(),
            'Key' => [
                $keyName => AttributeValue::create(['S' => $keyValue]),
            ],
        ]));

        $result->getAttributes();
    }

    protected function doFind(string $keyName, string $keyValue, ?bool $consistentRead = null): ?object
    {
        $result = $this->dynamoDbClient->getItem(new GetItemInput([
            'TableName' => $this->getTableName(),
            'ConsistentRead' => $consistentRead ?? false,
            'Key' => [
                $keyName => AttributeValue::create(['S' => $keyValue]),
            ],
        ]));

        $item = $result->getItem();

        return \count($item) > 0 ? $this->toObject($item) : null;
    }

    protected function doOptimisticPutWithRetries(
        object $instance,
        string $uniqueAttr,
        ?callable $reset = null,
        ?int $maxAttempts = null
    ): object {
        $maxAttempts ??= 10;
        $attempts = 0;

        do {
            $saved = true;

            if ($reset !== null) {
                $reset($instance);
            }

            try {
                $this->doPutItem($instance, $uniqueAttr);
            } catch (ConditionalCheckFailedException) {
                $saved = false;

                // Add exponential backoff to reduce CPU usage during contention
                if ($attempts < $maxAttempts - 1) {
                    \usleep((2 ** $attempts) * 10000); // 10ms, 20ms, 40ms, 80ms, etc.
                }
            }

            $attempts++;
        } while ($saved === false && $attempts < $maxAttempts);

        return $instance;
    }

    protected function doPaginate(
        QueryInput|ScanInput $input,
        ?int $limit = null,
        ?string $exclusiveStartKey = null
    ): iterable {
        $limit ??= 50;
        $count = 0;

        $input->setLimit($limit);
        $input->setTableName($this->getTableName());

        do {
            // Set this here so if we do need to loop more than once we have the updated key
            $exclusiveStartKey = $this->decodeLastEvaluatedKey($exclusiveStartKey ?? $this->lastEvaluatedKey);
            if ($exclusiveStartKey) {
                $input->setExclusiveStartKey($exclusiveStartKey);
            }

            $output = match (true) {
                $input instanceof QueryInput => $this->dynamoDbClient->query($input),
                $input instanceof ScanInput => $this->dynamoDbClient->scan($input),
            };

            $result = $this->resolveLastEvaluateKey($output);

            foreach ($result->getItems(true) as $item) {
                if ($count >= $limit) {
                    break;
                }

                yield $this->toObject($item);

                $count++;
            }

            // In some cases (e.g. search) we may get empty current page results but still have more pages to fetch
            // so we need to keep looping until we either run out of pages or reach the limit
        } while ($this->lastEvaluatedKey && $count < $limit);
    }

    protected function doPutItem(object $instance, ?string $uniqueAttr = null): object
    {
        $input = [
            'TableName' => $this->getTableName(),
            'Item' => $this->toItem($instance),
        ];

        if (StringHelper::isNotEmpty($uniqueAttr)) {
            $input['ConditionExpression'] = \sprintf('attribute_not_exists(%s)', $uniqueAttr);
        }

        $this->dynamoDbClient->putItem(new PutItemInput($input))->getAttributes();

        return $instance;
    }

    protected function getOptimisticPutObjectReset(): ?callable
    {
        return null;
    }

    protected function getOptimisticPutMaxAttempts(): ?int
    {
        return null;
    }

    protected function getTableName(): string
    {
        if (StringHelper::isNotEmpty($this->tablePrefix)) {
            return $this->tablePrefix . $this->defineTableName();
        }

        return $this->defineTableName();
    }

    protected function transformToItem(object $instance, ?array $context = null): ?array
    {
        // Extension point for repositories that want a simple transform without needing to implement the full interface
        return $this->itemObjectTransformer?->toItem($instance, $context);
    }

    protected function transformToObject(array $item, ?array $context = null): ?object
    {
        // Extension point for repositories that want a simple transform without needing to implement the full interface
        return $this->itemObjectTransformer?->toObject(static::getObjectClass(), $item, $context);
    }

    private function defineTableName(): string
    {
        if (StringHelper::isNotEmpty($this->tableName)) {
            return $this->tableName;
        }

        return $this->tableName = $this->tableNamingStrategy->classToTableName(static::getObjectClass());
    }

    private function resolveLastEvaluateKey(QueryOutput|ScanOutput $output): QueryOutput|ScanOutput
    {
        if ($output->getLastEvaluatedKey() === []) {
            $this->lastEvaluatedKey = null;

            return $output;
        }

        $key = \array_map(
            static fn ($v) => $v instanceof AttributeValue ? $v->requestBody() : $v,
            $output->getLastEvaluatedKey()
        );

        $this->lastEvaluatedKey = StringHelper::urlSafeBase64Encode(\json_encode($key));

        return $output;
    }

    private function toItem(object $instance): array
    {
        $item = $this->transformToItem($instance) ?? throw new RepositoryNotConfiguredException(\sprintf(
            'Failed to create item from object. Ensure the %s is set on the repository or override the "%s" method.',
            ItemObjectTransformerInterface::class,
            'transformToItem'
        ));

        return \array_filter($item, static fn (mixed $value): bool => $value instanceof AttributeValue);
    }

    private function toObject(array $item): object
    {
        return $this->transformToObject($item) ?? throw new RepositoryNotConfiguredException(\sprintf(
            'Failed to create object from item. Ensure the %s is set on the repository or override the "%s" method.',
            ItemObjectTransformerInterface::class,
            'transformToObject'
        ));
    }
}
