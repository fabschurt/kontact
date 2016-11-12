<?php

namespace FabSchurt\Kontact\Provider;

use FabSchurt\Kontact\Form\Type\KontactType;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Provider as SilexProvider;

/**
 * @author Fabien Schurter <fabien@fabschurt.com>
 */
final class FormServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function register(Container $container)
    {
        // Register original provider
        if (!isset($container['form.factory'])) {
            $container->register(new SilexProvider\FormServiceProvider());
        }

        // Parameters
        $container['form.kontact.max_message_length'] = 16384;

        // Services
        $container['form.type.kontact'] = function (Container $container): KontactType {
            return new KontactType(
                $container['request_stack']->getCurrentRequest()->request->all(),
                $container['form.kontact.max_message_length']
            );
        };
        $container->extend('form.types', function (array $types, Container $container): array {
            $types[] = $container['form.type.kontact'];

            return $types;
        });
    }
}
