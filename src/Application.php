<?php

/*
 * This file is part of the fabschurt/kontact package.
 *
 * (c) 2016 Fabien Schurter <fabien@fabschurt.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FabSchurt\Kontact;

use Dotenv\Dotenv;
use Dotenv\Loader;
use FabSchurt\Php\Utils\Config\EnvVarConfigParser;
use FabSchurt\Silex\Provider\Framework\FrameworkServiceProvider;
use Junker\Symfony\JSendErrorResponse;
use Silex\Application as SilexApplication;
use Silex\Provider as SilexProvider;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Fabien Schurter <fabien@fabschurt.com>
 */
final class Application extends SilexApplication
{
    /**
     * {@inheritDoc}
     */
    public function __construct(array $values = [])
    {
        $rootDir = __DIR__.'/..';
        $params  = (new EnvVarConfigParser(
            $rootDir,
            array_merge(['app.root_dir' => $rootDir], $values),
            [
                'environment'            => 'prod',
                'locale'                 => 'en',
                'mailer.port'            => 25,
                'mailer.message.subject' => 'Kontact',
            ]
        ))->parseConfig();
        $params['mailer.message.from_address'] = $params['mailer.message.from_address'] ?: $params['admin_email'];
        $params['mailer.message.to_address']   = $params['mailer.message.to_address']   ?: $params['admin_email'];
        $params['debug'] = in_array($params['environment'], ['dev', 'test'], true);
        parent::__construct($params);

        $this->register(new Provider\MailerServiceProvider());
        $this->register(new SilexProvider\LocaleServiceProvider(), ['locale' => $this['locale']]);
        $this->register(new SilexProvider\TranslationServiceProvider());
        $this->register(new SilexProvider\ValidatorServiceProvider());
        $this->register(new Provider\FormServiceProvider());
        $this->register(new SilexProvider\TwigServiceProvider());
        $this->register(new SilexProvider\ServiceControllerServiceProvider());
        $this->register(new FrameworkServiceProvider());
    }

    public function boot()
    {
        parent::boot();

        // Register custom JSend error handler
        $this->error(function (\Exception $e, Request $req, int $code): JSendErrorResponse {
            $data = [];
            if ($this['debug']) {
                $data = [
                    'class' => get_class($e),
                    'file'  => $e->getFile(),
                    'line'  => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ];
            }

            return new JSendErrorResponse($e->getMessage(), $e->getCode(), $data, $code);
        }, SilexApplication::EARLY_EVENT);
    }
}
