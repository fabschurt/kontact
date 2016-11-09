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

use FabSchurt\Kontact\Controller\KontactController;
use Pimple\{Container, ServiceProviderInterface};
use Silex\Api\{BootableProviderInterface, ControllerProviderInterface};
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Fabien Schurter <fabien@fabschurt.com>
 */
final class ControllerServiceProvider implements ServiceProviderInterface, BootableProviderInterface, ControllerProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function register(Container $container)
    {
        $container['controller.kontact'] = function () {
            return new KontactController();
        };
    }

    /**
     * {@inheritDoc}
     */
    public function boot(Application $app)
    {
        $app->mount('', $this);
    }

    /**
     * {@inheritDoc}
     */
    public function connect(Application $app)
    {
        $routes = $app['controllers_factory'];
        $routes
            ->match('/post', 'controller.kontact:postAction')
            ->bind('kontact.post')
            ->method(Request::METHOD_POST)
        ;

        return $routes;
    }
}
