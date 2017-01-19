<?php

/*
 * This file is part of the fabschurt/kontact package.
 *
 * (c) 2016 Fabien Schurter <fabien@fabschurt.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FabSchurt\Kontact\Controller;

use FabSchurt\Kontact\Form\Type\KontactType;
use Junker\Symfony\JSendFailResponse;
use Junker\Symfony\JSendResponse;
use Junker\Symfony\JSendSuccessResponse;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Fabien Schurter <fabien@fabschurt.com>
 */
final class KontactController
{
    /**
     * @param Request     $request
     * @param Application $app
     *
     * @return JSendResponse
     */
    public function postAction(Request $request, Application $app): JSendResponse
    {
        $form = $app['form.factory']->createNamed('', KontactType::class);
        $form->handleRequest($request);
        if (!$form->isSubmitted()) {
            return $app->abort(Response::HTTP_FORBIDDEN, 'Invalid POST data.');
        }
        if ($form->isValid()) {
            $app['mailer']->send($app['mailer.message.factory']($form->getData()));

            return new JSendSuccessResponse();
        }

        return new JSendFailResponse($app['form.error_flattener']->flattenFormErrors($form));
    }
}
