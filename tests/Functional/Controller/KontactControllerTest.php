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
use Silex\Application as SilexApplication;
use Silex\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Fabien Schurter <fabien@fabschurt.com>
 */
final class KontactControllerTest extends WebTestCase
{
    /**
     * @testdox ->postAction() ~ It returns a success JSend response and send a contact e-mail
     */
    public function testPostAction1()
    {
        $this->client->request(Request::METHOD_POST, '/post', [
            'name'    => 'John Doe',
            'address' => 'john.doe@example.org',
            'message' => 'Hello, is it me you’re looking for?',
        ]);
        $response = $this->client->getResponse();

        verify($response->isOk())->true();
        verify(json_decode($response->getContent(), true))->same([
            'status' => 'success',
            'data'   => null,
        ]);
        verify($this->app['mailer.message_logger']->countMessages())->same(1);

        $message = $this->app['mailer.message_logger']->getMessages()[0];
        verify($message->getSubject())->same('Kontact');
        verify($message->getFrom())->same(['jason.bourne@example.org' => null]);
        verify($message->getTo())->same(['jason.bourne@example.org' => null]);
        verify($message->getBody())->same(<<<'BODY'
Name : John Doe

E-mail address : john.doe@example.org

Message :

Hello, is it me you’re looking for?

BODY
        );
    }

    /**
     * @testdox ->postAction() ~ It returns a fail JSend response if there are blank request params
     */
    public function testPostAction2()
    {
        $this->client->request(Request::METHOD_POST, '/post', [
            'name'    => '',
            'address' => null,
            'message' => '',
        ]);
        $response = $this->client->getResponse();

        verify($response->isOk())->true();
        verify(json_decode($response->getContent(), true))->same([
            'status' => 'fail',
            'data'   => [
                'name'    => ['This value should not be blank.'],
                'address' => ['This value should not be blank.'],
                'message' => ['This value should not be blank.'],
            ],
        ]);
        verify($this->app['mailer.message_logger']->countMessages())->same(0);
    }

    /**
     * @testdox ->postAction() ~ It returns a fail JSend response if there are extraneous request params
     */
    public function testPostAction3()
    {
        $this->client->request(Request::METHOD_POST, '/post', [
            'message'          => 'Hi, I’m Jane Doe.',
            'extraneous_param' => 'Forbidden',
        ]);
        $response = $this->client->getResponse();

        verify($response->isOk())->true();
        verify(json_decode($response->getContent(), true))->same([
            'status' => 'fail',
            'data'   => [
                'global' => ['This form should not contain extra fields.'],
            ],
        ]);
        verify($this->app['mailer.message_logger']->countMessages())->same(0);
    }

    /**
     * @testdox ->postAction() ~ The sent e-mail is customizable via application params
     */
    public function testPostAction4()
    {
        $this->app['mailer.message.subject']      = 'What time is it?';
        $this->app['mailer.message.from_address'] = 'finn@ooo.land';
        $this->app['mailer.message.from_name']    = 'Finn the human';
        $this->app['mailer.message.to_address']   = 'jake@ooo.land';
        $this->app['mailer.message.to_name']      = 'Jake the dog';
        $this->client->request(Request::METHOD_POST, '/post', ['message' => 'Adventure Time!']);
        $response = $this->client->getResponse();

        verify($response->isOk())->true();
        verify($this->app['mailer.message_logger']->countMessages())->same(1);

        $message = $this->app['mailer.message_logger']->getMessages()[0];
        verify($message->getSubject())->same('What time is it?');
        verify($message->getFrom())->same(['finn@ooo.land' => 'Finn the human']);
        verify($message->getTo())->same(['jake@ooo.land' => 'Jake the dog']);
    }

    /**
     * @param string $captchaValue
     * @param bool   $mailWasSent
     * @param array  $expectedResponse
     *
     * @dataProvider provideDataForTestPostAction5
     *
     * @testdox ->postAction() ~ The form can be protected by a captcha
     */
    public function testPostAction5(string $captchaValue, bool $mailWasSent, array $expectedResponse)
    {
        $this->app->before(function (Request $request, SilexApplication $app) {
            $app['captcha']->generate('4-8-15-16-23-42');
        });
        $this->client->request(Request::METHOD_POST, '/post', [
            'message' => 'Message in a bottle.',
            'captcha' => $captchaValue,
        ]);
        $response = $this->client->getResponse();

        verify($response->isOk())->true();
        verify($this->app['mailer.message_logger']->countMessages())->same((int) $mailWasSent);
        verify(json_decode($response->getContent(), true))->same($expectedResponse);
    }

    /**
     * @return array
     */
    public function provideDataForTestPostAction5(): array
    {
        return [
            ['', false, [
                'status' => 'fail',
                'data' => [
                    'captcha' => [
                        'This value should not be blank.',
                        'Incorrect captcha value.',
                    ],
                ],
            ]],
            ['zbleurg', false, [
                'status' => 'fail',
                'data' => [
                    'captcha' => [
                        'Incorrect captcha value.',
                    ],
                ],
            ]],
            ['4-8-15-16-23-42', true, [
                'status' => 'success',
                'data' => null,
            ]],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function createApplication(): Application
    {
        $app = new Application([
            'environment'    => 'test',
            'locale'         => 'en',
            'admin_email'    => 'jason.bourne@example.org',
            'enable_captcha' => $this->getName(false) === 'testPostAction5',
        ]);
        unset($app['exception_handler']);

        $app['swiftmailer.use_spool'] = false;
        $app['swiftmailer.transport'] = function (Container $container): \Swift_Transport {
            return new \Swift_Transport_NullTransport($container['swiftmailer.transport.eventdispatcher']);
        };
        $app['mailer.message_logger'] = function (): \Swift_Plugins_MessageLogger {
            return new \Swift_Plugins_MessageLogger();
        };
        $app->extend('mailer', function (\Swift_Mailer $mailer) use ($app): \Swift_Mailer {
            $mailer->registerPlugin($app['mailer.message_logger']);

            return $mailer;
        });

        return $app;
    }

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->setBeStrictAboutChangesToGlobalState(false);

        parent::setUp();

        $this->client = $this->createClient();
    }
}
