<?php

/*
 * This file is part of the fabschurt/kontact package.
 *
 * (c) 2016 Fabien Schurter <fabien@fabschurt.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FabSchurt\Kontact\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Provider as SilexProvider;

/**
 * @author Fabien Schurter <fabien@fabschurt.com>
 */
final class TranslationServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function register(Container $container)
    {
        // Register original provider
        if (!isset($container['translator'])) {
            $container->register(new SilexProvider\TranslationServiceProvider());
        }

        // Parameters
        $container['translator.app_resource_dir'] = "{$container['root_dir']}/app/translations";

        // Services
        $container['translator.resources'] = function (Container $container) {
            return [
                [
                    'xliff',
                    "{$container['translator.app_resource_dir']}/kontact/kontact.{$container['locale']}.xlf",
                    $container['locale'],
                    'kontact',
                ],
            ];
        };
    }
}
