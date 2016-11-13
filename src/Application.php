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
        if (
            is_file("{$rootDir}/.env") &&
            getenv('ENVIRONMENT') !== 'test' &&
            ($values['env'] ?? null) !== 'test')
        ) {
            $dotenv = new Dotenv($rootDir);
            $dotenv->load();
        }
        $values = array_merge([
            'app_name'    => 'kontact',
            'root_dir'    => $rootDir,
            'env'         => getenv('ENVIRONMENT') ?: 'prod',
            'debug'       => in_array(getenv('ENVIRONMENT'), ['dev', 'test'], true),
            'admin_email' => getenv('ADMIN_EMAIL'),
        ], $values);
        parent::__construct($values);

        // Register providers
        $this->register(new Provider\SwiftmailerServiceProvider());
        $this->register(new Provider\FormServiceProvider());
        $this->register(new SilexProvider\ServiceControllerServiceProvider());
        $this->register(new Provider\ControllerServiceProvider());
    }
}
