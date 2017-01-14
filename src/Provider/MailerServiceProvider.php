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
final class MailerServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function register(Container $container)
    {
        $container->register(new SilexProvider\SwiftmailerServiceProvider(), [
            'swiftmailer.options' => [
                'host'       => $container['mailer.host'],
                'port'       => $container['mailer.port'],
                'username'   => $container['mailer.username'],
                'password'   => $container['mailer.password'],
                'encryption' => $container['mailer.encryption'],
                'auth_mode'  => $container['mailer.auth_mode'],
            ],
        ]);

        $container['mailer.message.factory'] = $container->protect(
            /**
             * Returns a pre-configured `\Swift_Message` instance, drawing body
             * content context from validated form data.
             *
             * @param array $data The data array from the validated form
             *
             * @return \Swift_Message
             */
            function (array $data) use ($container): \Swift_Message {
                return \Swift_Message::newInstance()
                    ->setSubject($container['mailer.message.subject'])
                    ->setFrom($container['mailer.message.from_address'], $container['mailer.message.from_name'])
                    ->setTo($container['mailer.message.to_address'], $container['mailer.message.to_name'])
                    ->setBody($container['twig']->render('message.txt.twig', $data))
                    ->setContentType('text/plain')
                ;
            }
        );
    }
}
