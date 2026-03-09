<?php
declare(strict_types=1);

namespace NatePage\DynamoDbRepository\Tests\Common\Naming;

use NatePage\DynamoDbRepository\Common\Naming\UnderscoreTableNamingStrategy;
use NatePage\DynamoDbRepository\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class UnderscoreTableNamingStrategyTest extends AbstractTestCase
{
    public static function provideClassToTableName(): iterable
    {
        yield ['App\Entity\User', 'user'];
        yield ['App\Entity\BlogPost', 'blog_post'];
        yield ['App\Entity\Comment', 'comment'];
        yield ['App\Entity\MyCustomEntity', 'my_custom_entity'];
        yield ['NoNamespaceClass', 'no_namespace_class'];
        yield ['unchanged', 'unchanged'];
    }

    #[DataProvider('provideClassToTableName')]
    public function testClassToTableName(string $input, string $expected): void
    {
        $strategy = new UnderscoreTableNamingStrategy();

        self::assertEquals($expected, $strategy->classToTableName($input));
    }
}
