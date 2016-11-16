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
final class TwigServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function register(Container $container)
    {
        // Register original provider
        if (!isset($container['twig'])) {
            $container->register(new SilexProvider\TwigServiceProvider(), [
                'twig.path'    => "{$container['root_dir']}/app/views",
                'twig.options' => [
                    'cache' => "{$container['root_dir']}/var/cache/twig",
                ],
            ]);
        }
    }
}
