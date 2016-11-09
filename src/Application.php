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
use Silex\{Application as SilexApplication, Provider as SilexProvider};

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
        // Define application root
        $rootDir = __DIR__.'/..';

        // Load and validate environment config
        if (is_file("{$rootDir}/.env")) {
            $dotenv = new Dotenv($rootDir);
            $dotenv->load();
        }

        // Base configuration
        $values = array_merge($values, [
            'name'              => 'kontact',
            'root_dir'          => $rootDir,
            'env'               => getenv('ENVIRONMENT') ?: 'prod',
            'admin_email'       => getenv('ADMIN_EMAIL'),
            'mailer.host'       => getenv('MAILER_HOST'),
            'mailer.port'       => getenv('MAILER_PORT'),
            'mailer.username'   => getenv('MAILER_USERNAME'),
            'mailer.password'   => getenv('MAILER_PASSWORD'),
            'mailer.encryption' => getenv('MAILER_ENCRYPTION'),
            'mailer.auth_mode'  => getenv('MAILER_AUTH_MODE'),
        ]);
        parent::__construct($values);

        // Activate debug mode if needed
        $this['debug'] = ($this['env'] === 'dev');

        // Helpers
        $this->register(new Provider\MailerServiceProvider());

        // Controller and routing
        $this->register(new SilexProvider\ServiceControllerServiceProvider());
        $this->register(new Provider\ControllerServiceProvider());
    }
}
