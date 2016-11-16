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
use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\Form\FormInterface;

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
        $container['form.error_flattener'] = $container->protect(
            /**
             * Transforms a form error iterator into an flattened array.
             *
             * @param FormErrorIterator $errors Form error iterator returned by `{@see FormInterface::getErrors()}`
             *
             * @return array A list of error messages, grouped by field names
             */
            function (FormErrorIterator $errors): array {
                $flatArray = [];
                foreach ($errors as $error) {
                    $flatArray[$error->getOrigin()->getName() ?: 'errors'][] = $error->getMessage();
                }

                return $flatArray;
            }
        );
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
