<?php
declare(strict_types=1);

namespace NatePage\DynamoDbRepository\Tests\Fixture\Kernel;

use AutoMapper\Symfony\Bundle\AutoMapperBundle;
use NatePage\DynamoDbRepository\Bundle\DynamoDbRepositoryBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

final class TestKernel extends Kernel
{
    public function __construct(
        string $environment,
        bool $debug,
        private readonly array $configFiles = [],
    ) {
        parent::__construct($environment, $debug);
    }

    public function getProjectDir(): string
    {
        return __DIR__;
    }

    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();
        yield new AutoMapperBundle();
        yield new DynamoDbRepositoryBundle();
    }

    /**
     * @throws \Exception
     */
    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load($this->getProjectDir() . '/config/default.php');

        foreach ($this->configFiles as $file) {
            $loader->load($file);
        }
    }
}
