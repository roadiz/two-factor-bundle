<?php

declare(strict_types=1);

namespace RZ\Roadiz\TwoFactorBundle\DependencyInjection\Compiler;

use RuntimeException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DoctrineMigrationCompilerPass implements CompilerPassInterface
{
    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container): void
    {
        if ($container->hasDefinition('doctrine.migrations.configuration')) {
            $configurationDefinition = $container->getDefinition('doctrine.migrations.configuration');
            $ns = 'RZ\Roadiz\TwoFactorBundle\Migrations';
            $path = '@RoadizTwoFactorBundle/migrations';

            $path = $this->checkIfBundleRelativePath($path, $container);
            $configurationDefinition->addMethodCall('addMigrationsDirectory', [$ns, $path]);
        }
    }

    private function checkIfBundleRelativePath(string $path, ContainerBuilder $container): string
    {
        if (isset($path[0]) && $path[0] === '@') {
            $pathParts  = explode('/', $path);
            $bundleName = substr($pathParts[0], 1);

            $bundlePath = $this->getBundlePath($bundleName, $container);

            return $bundlePath . substr($path, strlen('@' . $bundleName));
        }

        return $path;
    }

    private function getBundlePath(string $bundleName, ContainerBuilder $container): string
    {
        $bundleMetadata = $container->getParameter('kernel.bundles_metadata');
        assert(is_array($bundleMetadata));

        if (! isset($bundleMetadata[$bundleName])) {
            throw new RuntimeException(
                sprintf(
                    'The bundle "%s" has not been registered, available bundles: %s',
                    $bundleName,
                    implode(', ', array_keys($bundleMetadata))
                )
            );
        }

        return $bundleMetadata[$bundleName]['path'];
    }
}
