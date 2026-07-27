<?php
declare(strict_types=1);

namespace NatePage\DynamoDbRepository\AutoMapper\Transformer;

use AsyncAws\DynamoDb\ValueObject\AttributeValue;
use AutoMapper\Generator\UniqueVariableScope;
use AutoMapper\Metadata\PropertyMetadata;
use NatePage\Utils\Helper\StringHelper;
use PhpParser\Node\Arg;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr;
use PhpParser\Node\MatchArm;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt;

final class ToAttributeValueTransformer extends AbstractAttributeValueTransformer
{
    public function transform(
        Expr $input,
        Expr $target,
        PropertyMetadata $propertyMapping,
        UniqueVariableScope $uniqueVariableScope,
        Expr $source,
        ?Expr $existingValue = null
    ): array {
        [$output, $transformStatements] = $this->valueTransformer->transform(
            $input,
            $target,
            $propertyMapping,
            $uniqueVariableScope,
            $source,
            $existingValue
        );

        $newValue = $output;
        $stmts = [];
        $true = new Expr\ConstFetch(new Name('true'));

        // If output is anything else than variable, create a new variable so we can update its value
        if ($newValue instanceof Expr\Variable === false) {
            $newValue = new Expr\Variable($uniqueVariableScope->getUniqueName('value'));
            $transformStatements[] = new Stmt\Expression(new Expr\Assign($newValue, $output));
        }

        /**
         * When default string for null values set:
         *
         * if ($value === null) {
         *     $value = new AttributeValue(['S' => '__DEFAULT_STRING__']);
         * }
         *
         * Otherwise, default to null:
         *
         * if ($value === null) {
         *      $value = new AttributeValue(['NULL' => true]);
         *  }
         */
        $nullKey = StringHelper::isNotEmpty($this->defaultStringIfNull) ? 'S' : 'NULL';
        $nullValue = StringHelper::isNotEmpty($this->defaultStringIfNull) ? new String_($this->defaultStringIfNull) : $true;
        $stmts[] = new Stmt\If_(new Expr\BinaryOp\Identical($newValue, new Expr\ConstFetch(new Name('null'))), [
            'stmts' => [
                new Stmt\Expression(new Expr\Assign($newValue, new Expr\New_(new Name(AttributeValue::class), [
                    new Arg(new Expr\Array_([
                        new ArrayItem($nullValue, new String_($nullKey)),
                    ]))
                ]))),
            ],
        ]);

        /**
         * When array as JSON string is enabled:
         *
         * if (\is_array($value) === true) {
         *     $value = new AttributeValue(['S' => \json_encode($value)]);
         * }
         */
        if ($this->arrayAsJsonString) {
            $stmts[] = $this->stmtIfTrue(new Expr\FuncCall(new Name('\is_array'), [new Arg($newValue)]), [
                new Stmt\Expression(new Expr\Assign($newValue, new Expr\New_(new Name(AttributeValue::class), [
                    new Arg(new Expr\Array_([
                        new ArrayItem(
                            new Expr\FuncCall(new Name('\json_encode'), [new Arg($newValue)]),
                            new String_('S')
                        ),
                    ]))
                ]))),
            ]);
        }

        /**
         * Handle built-in types:
         * (DynamoDB requires numbers to be cast as string)
         *
         * if ($value instanceof AttributeValue === false) {
         *     $key = match (true) {
         *         \is_bool($value) => 'BOOL',
         *         \is_int($value), \is_float($value) => 'N',
         *         default => 'S',
         *     }
         *
         *     $value = match (true) {
         *         \is_int($value), \is_float($value) => (string)$value,
         *         default => $value,
         *     }
         *
         *     $value = new AttributeValue([$key => $value]);
         * }
         */
        $matchKey = new Expr\Match_($true, [
            new MatchArm([new Expr\FuncCall(new Name('\is_bool'), [new Arg($newValue)])], new String_('BOOL')),
            new MatchArm([
                new Expr\FuncCall(new Name('\is_int'), [new Arg($newValue)]),
                new Expr\FuncCall(new Name('\is_float'), [new Arg($newValue)]),
            ], new String_('N')),
            new MatchArm([new Expr\ConstFetch(new Name('default'))], new String_('S')),
        ]);
        $key = new Expr\Variable($uniqueVariableScope->getUniqueName('key'));

        $matchValue = new Expr\Match_($true, [
            new MatchArm([
                new Expr\FuncCall(new Name('\is_int'), [new Arg($newValue)]),
                new Expr\FuncCall(new Name('\is_float'), [new Arg($newValue)]),
            ], new Expr\Cast\String_($newValue)),
            new MatchArm([new Expr\ConstFetch(new Name('default'))], $newValue),
        ]);

        $stmts[] = $this->stmtIfFalse(new Expr\Instanceof_($newValue, new Name(AttributeValue::class)), [
            new Stmt\Expression(new Expr\Assign($key, $matchKey)),
            new Stmt\Expression(new Expr\Assign($newValue, $matchValue)),
            new Stmt\Expression(new Expr\Assign($newValue, new Expr\New_(new Name(AttributeValue::class), [
                new Arg(new Expr\Array_([
                    new ArrayItem($newValue, $key),
                ]))
            ]))),
        ]);

        $transformStatements[] = $this->stmtIfEnabled($stmts);

        return [$newValue, $transformStatements];
    }
}
