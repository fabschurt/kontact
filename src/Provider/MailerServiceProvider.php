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

use Pimple\{Container, ServiceProviderInterface};
use Silex\Api\BootableProviderInterface;
use Silex\Application;
use Silex\Provider as SilexProvider;

/**
 * @author Fabien Schurter <fabien@fabschurt.com>
 */
final class MailerServiceProvider implements ServiceProviderInterface, BootableProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function register(Container $container)
    {
        // Register original provider
        if (!isset($container['mailer'])) {
            $container->register(new SilexProvider\SwiftmailerServiceProvider());
        }

        // Parameters
        $container['swiftmailer.options'] = [
            'host'       => $container['mailer.host'],
            'port'       => $container['mailer.port'],
            'username'   => $container['mailer.username'],
            'password'   => $container['mailer.password'],
            'encryption' => $container['mailer.encryption'],
            'auth_mode'  => $container['mailer.auth_mode'],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function boot(Application $app)
    {
        if ($app['debug']) {
            $app['mailer']->registerPlugin(new \Swift_Plugins_RedirectingPlugin($app['admin_email']));
        }
    }
}
