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

use Junker\Symfony\JSendErrorResponse;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Api\BootableProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Fabien Schurter <fabien@fabschurt.com>
 */
final class ViewServiceProvider implements ServiceProviderInterface, BootableProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function register(Container $container)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function boot(Application $app)
    {
        // Send required CORS header
        $app->after(function (Request $request, Response $response) {
            $response->headers->set('Access-Control-Allow-Origin', '*');
        });

        // Register custom JSend error handler
        $app->error(function (\Exception $e, Request $request, int $code) use ($app): JSendErrorResponse {
            $data = [];
            if ($app['debug']) {
                $data = [
                    'class' => get_class($e),
                    'file'  => $e->getFile(),
                    'line'  => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ];
            }

            return new JSendErrorResponse($e->getMessage(), $e->getCode(), $data, $code);
        }, Application::EARLY_EVENT);
    }
}
