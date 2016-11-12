<?php

/*
 * This file is part of the fabschurt/kontact package.
 *
 * (c) 2016 Fabien Schurter <fabien@fabschurt.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FabSchurt\Kontact\Tests\Functional\Controller;

use FabSchurt\Kontact\Application;
use Pimple\Container;
use Silex\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Fabien Schurter <fabien@fabschurt.com>
 */
final class KontactControllerTest extends WebTestCase
{
    public function testPost()
    {
        $this->client->request(Request::METHOD_POST, '/post', [
            'name'    => 'John Doe',
            'address' => 'john.doe@example.org',
            'message' => 'Hello, is it me you’re looking for?',
        ]);
        $response = $this->client->getResponse();
        verify($response->isOk())->true();
        verify(json_decode($response->getContent()))->same([
            'status' => 'success',
            'data'   => null,
        ]);
        verify($this->app['mailer.message_logger']->countMessages())->same(1);
    }

    public function testPostWithBlankParameters()
    {
        $this->client->request(Request::METHOD_POST, '/post', [
            'name'    => '',
            'address' => null,
            'message' => '',
        ]);
        $response = $this->client->getResponse();
        verify($response->isOk())->true();
        verify(json_decode($response->getContent()))->same([
            'status' => 'fail',
            'data'   => [
                'name'    => 'This value should not be blank.',
                'address' => 'This value should not be blank.',
                'message' => 'This value should not be blank.',
            ],
        ]);
        verify($this->app['mailer.message_logger']->countMessages())->same(0);
    }

    public function testPostWithExtraneousParameters()
    {
        $this->client->request(Request::METHOD_POST, '/post', [
            'name'             => 'Jane Doe',
            'extraneous_param' => 'This is not right.',
        ]);
        $response = $this->client->getResponse();
        verify($response->isOk())->true();
        verify(json_decode($response->getContent()))->same([
            'status' => 'fail',
            'data'   => [
                'extraneous_param' => 'This extra parameter is not allowed.',
            ],
        ]);
        verify($this->app['mailer.message_logger']->countMessages())->same(0);
    }

    /**
     * {@inheritDoc}
     */
    public function createApplication(): Application
    {
        $app = new Application([
            'debug'                 => true,
            'session.test'          => true,
            'swiftmailer.use_spool' => false,
            'swiftmailer.transport' => function (Container $container) {
                return new \Swift_Transport_NullTransport($container['swiftmailer.transport.eventdispatcher']);
            },
            'mailer.message_logger' => function () {
                return new \Swift_Plugins_MessageLogger();
            },
        ]);
        $app['mailer']->registerPlugin($app['mailer.message_logger']);
        unset($app['exception_handler']);

        return $app;
    }

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->client = $this->createClient();
    }
}
