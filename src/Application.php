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
        $config  = (new EnvVarConfigParser(
            new Dotenv($rootDir),
            new Loader(null),
            "{$rootDir}/.env.example",
            array_merge(
                $values,
                ['app.root_dir' => $rootDir]
            )
        ))->parseConfig();
        parent::__construct($config);

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
