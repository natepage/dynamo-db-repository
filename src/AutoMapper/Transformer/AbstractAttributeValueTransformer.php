<?php
declare(strict_types=1);

namespace NatePage\DynamoDbRepository\AutoMapper\Transformer;

use AutoMapper\Transformer\DependentTransformerInterface;
use AutoMapper\Transformer\TransformerInterface;
use NatePage\DynamoDbRepository\AutoMapper\Mapper\AutoMapperItemObjectMapper;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;

abstract class AbstractAttributeValueTransformer implements TransformerInterface, DependentTransformerInterface
{
    public function __construct(
        protected TransformerInterface $valueTransformer,
        protected bool $arrayAsJsonString = true,
        protected ?string $defaultStringIfNull = null,
        protected ?bool $isArrayProperty = null,
    ) {
    }

    public function getDependencies(): array
    {
        return $this->valueTransformer instanceof DependentTransformerInterface
            ? $this->valueTransformer->getDependencies()
            : [];
    }

    /**
     * Returns:
     * if (($context[AutoMapperItemObjectMapper::CONTEXT_KEY] ?? false) === true) {
     *     ...$stmts
     * }
     *
     * @param Stmt[] $stmts
     */
    protected function stmtIfEnabled(array $stmts): Stmt\If_
    {
        $flagInContext = new Expr\ArrayDimFetch(
            new Expr\Variable('context'),
            new Expr\ClassConstFetch(new Name\FullyQualified(AutoMapperItemObjectMapper::class), 'CONTEXT_KEY')
        );

        return $this->stmtIfTrue(
            new Expr\BinaryOp\Coalesce($flagInContext, new Expr\ConstFetch(new Name('false'))),
            $stmts
        );
    }

    /**
     * @param Stmt[] $stmts
     */
    protected function stmtIfFalse(Expr $left, array $stmts): Stmt\If_
    {
        return new Stmt\If_(
            new Expr\BinaryOp\Identical($left, new Expr\ConstFetch(new Name('false'))),
            ['stmts' => $stmts]
        );
    }

    /**
     * @param Stmt[] $stmts
     */
    protected function stmtIfTrue(Expr $left, array $stmts): Stmt\If_
    {
        return new Stmt\If_(
            new Expr\BinaryOp\Identical($left, new Expr\ConstFetch(new Name('true'))),
            ['stmts' => $stmts]
        );
    }
}
