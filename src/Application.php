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

use FabSchurt\Php\Utils\Config\EnvVarConfigParser;
use FabSchurt\Silex\Provider\Framework\FrameworkServiceProvider;
use Monolog\Logger;
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
        $rootDir  = __DIR__.'/..';
        $defaults = [
            'environment'            => 'prod',
            'locale'                 => 'en',
            'mailer.port'            => 25,
            'mailer.message.subject' => 'Kontact',
        ];
        $params = (new EnvVarConfigParser(
            $rootDir,
            array_merge(['app.root_dir' => $rootDir], $values),
            $defaults
        ))->parseConfig();
        $params['mailer.message.from_address'] = $params['mailer.message.from_address'] ?: $params['admin_email'];
        $params['mailer.message.to_address']   = $params['mailer.message.to_address'] ?: $params['admin_email'];
        $params['debug'] = in_array($params['environment'], ['dev', 'test'], true);
        parent::__construct($params);

        $this->register(new Provider\MailerServiceProvider());
        $this->register(new SilexProvider\LocaleServiceProvider(), [
            'locale' => $this['locale'],
        ]);
        $this->register(new SilexProvider\TranslationServiceProvider());
        $this->register(new SilexProvider\ValidatorServiceProvider());
        $this->register(new Provider\FormServiceProvider());
        $this->register(new SilexProvider\TwigServiceProvider());
        $this->register(new SilexProvider\ServiceControllerServiceProvider());
        $this->register(new Provider\ViewServiceProvider());
        $this->register(new SilexProvider\MonologServiceProvider(), [
            'monolog.level' => $this['debug'] ? Logger::DEBUG : Logger::ERROR,
        ]);
        $this->register(new FrameworkServiceProvider());
    }
}
