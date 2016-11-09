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
use Junker\Symfony\JSendErrorResponse;
use Pimple\{Container, ServiceProviderInterface};
use Silex\Api\{BootableProviderInterface, ControllerProviderInterface};
use Silex\{Application, ControllerCollection};
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

        // Register custom error handler
        $app->error(function (\Exception $e, Request $req, int $code) use ($app): JSendErrorResponse {
            $data = [];
            if ($app['debug']) {
                $data = [
                    'file'  => $e->getFile(),
                    'line'  => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ];
            }

            return new JSendErrorResponse($e->getMessage(), $e->getCode(), $data, $code);
        });
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
