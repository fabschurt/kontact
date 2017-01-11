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

/**
 * @author Fabien Schurter <fabien@fabschurt.com>
 */
final class KontactController
{
    /**
     * @param Request     $req
     * @param Application $app
     *
     * @return JSendResponse
     */
    public function postAction(Request $req, Application $app): JSendResponse
    {
        $form = $app['form.factory']->createNamed('', KontactType::class);
        $form->handleRequest($req);
        if ($form->isSubmitted() && $form->isValid()) {
            $sentCount = $app['mailer']->send($app['mailer.message.factory']($form->getData()));
            if (!$sentCount) {
                $this->abort(500, 'There has been an error when trying to send the e-mail.');
            }

            return new JSendSuccessResponse();
        }

        return new JSendFailResponse($app['form.error_flattener']->flattenFormErrors($form));
    }
}
