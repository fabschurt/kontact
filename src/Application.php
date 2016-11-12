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
use Silex\Application as SilexApplication;
use Silex\Provider as SilexProvider;

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
        // Initialize app
        $rootDir = __DIR__.'/..';
        if (is_file("{$rootDir}/.env")) {
            $dotenv = new Dotenv($rootDir);
            $dotenv->load();
        }
        $values = array_merge([
            'name'              => 'kontact',
            'root_dir'          => $rootDir,
            'env'               => getenv('ENVIRONMENT') ?: 'prod',
            'debug'             => getenv('ENVIRONMENT') === 'dev',
            'admin_email'       => getenv('ADMIN_EMAIL'),
            'mailer.host'       => getenv('MAILER_HOST'),
            'mailer.port'       => getenv('MAILER_PORT'),
            'mailer.username'   => getenv('MAILER_USERNAME'),
            'mailer.password'   => getenv('MAILER_PASSWORD'),
            'mailer.encryption' => getenv('MAILER_ENCRYPTION'),
            'mailer.auth_mode'  => getenv('MAILER_AUTH_MODE'),
        ], $values);
        parent::__construct($values);

        // Register providers
        $this->register(new Provider\SwiftmailerServiceProvider());
        $this->register(new SilexProvider\ServiceControllerServiceProvider());
        $this->register(new Provider\ControllerServiceProvider());
    }
}
