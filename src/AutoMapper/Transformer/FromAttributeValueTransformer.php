<?php
declare(strict_types=1);

namespace NatePage\DynamoDbRepository\AutoMapper\Transformer;

use AsyncAws\DynamoDb\ValueObject\AttributeValue;
use AutoMapper\Generator\UniqueVariableScope;
use AutoMapper\Metadata\PropertyMetadata;
use AutoMapper\Transformer\AbstractArrayTransformer;
use NatePage\Utils\Helper\StringHelper;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt;

final class FromAttributeValueTransformer extends AbstractAttributeValueTransformer
{
    public function transform(
        Expr $input,
        Expr $target,
        PropertyMetadata $propertyMapping,
        UniqueVariableScope $uniqueVariableScope,
        Expr $source,
        ?Expr $existingValue = null
    ): array {
        $stmts = [];
        $getStringCall = new Expr\NullsafeMethodCall($input, 'getS');

        /**
         * When default string for null values set:
         *
         * if ($input->getNull() || $input->getS() === '__DEFAULT_STRING__') {
         *     $input = null;
         * }
         */
        $defaultString = StringHelper::isNotEmpty($this->defaultStringIfNull)
            ? $this->defaultStringIfNull
            : \md5(\uniqid((string)\mt_rand(), true)); // Random string nobody would ever use
        $stmts[] = new Stmt\If_(
            new Expr\BinaryOp\BooleanOr(
                new Expr\MethodCall($input, 'getNull'),
                new Expr\BinaryOp\Identical(new Expr\MethodCall($input, 'getS'), new String_($defaultString))
            ),
            [
                'stmts' => [
                    new Stmt\Expression(new Expr\Assign($input, new Expr\ConstFetch(new Name('null')))),
                ],
            ]
        );

        /**
         * When array as JSON string is enabled:
         *
         * if (\is_string($input?->getS()) && \json_validate($input?->getS()) {
         *     $input = \json_decode($input?->getS(), true);
         * }
         */
        if ($this->valueTransformer instanceof AbstractArrayTransformer || ($this->isArrayProperty ?? false)) {
            $stmts[] = new Stmt\If_(
                new Expr\BinaryOp\BooleanAnd(
                    new Expr\FuncCall(new Name('\is_string'), [new Arg($getStringCall)]),
                    new Expr\FuncCall(new Name('\json_validate'), [new Arg($getStringCall)])
                ),
                [
                    'stmts' => [
                        new Stmt\Expression(
                            new Expr\Assign(
                                $input,
                                new Expr\FuncCall(new Name('\json_decode'), [
                                    new Arg($getStringCall),
                                    new Arg(new Expr\ConstFetch(new Name('true'))),
                                ])
                            )
                        ),
                    ],
                ]
            );
        }

        /**
         * Handle built-in types:
         *
         * if ($input instanceof AttributeValue && $input->getS() !== null) {
         *     $input = $input->getS();
         * }
         */
        foreach (['getBool', 'getS', 'getN'] as $methodName) {
            $stmts[] = new Stmt\If_(
                new Expr\BinaryOp\BooleanAnd(
                    new Expr\Instanceof_($input, new Name(AttributeValue::class)),
                    new Expr\BinaryOp\NotIdentical(new Expr\MethodCall($input, $methodName), new Expr\ConstFetch(new Name('null')))
                ),
                [
                    'stmts' => [
                        new Stmt\Expression(new Expr\Assign($input, new Expr\MethodCall($input, $methodName))),
                    ],
                ]
            );
        }

        [$output, $transformStatements] = $this->valueTransformer->transform(
            $input,
            $target,
            $propertyMapping,
            $uniqueVariableScope,
            $source,
            $existingValue
        );

        \array_unshift(
            $transformStatements,
            $this->stmtIfEnabled([
                $this->stmtIfTrue(new Expr\Instanceof_($input, new Name(AttributeValue::class)), $stmts),
            ])
        );

        return [$output, $transformStatements];
    }
}
