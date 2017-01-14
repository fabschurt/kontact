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
        $container->register(new SilexProvider\FormServiceProvider());

        $container['form.kontact.max_message_length'] = 16384;

        $container['form.type.kontact'] = function (Container $container): KontactType {
            return new KontactType(
                $container['request_stack']->getCurrentRequest()->request->all(),
                $container['form.kontact.max_message_length'],
                $container['enable_captcha']
            );
        };

        $container->extend('form.types', function (array $types, Container $container): array {
            $types[] = $container['form.type.kontact'];

            return $types;
        });
    }
}
