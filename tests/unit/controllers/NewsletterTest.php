<?php
/**
 * @link https://www.simplonprod.co
 * @copyright Copyright (c) 2021 Simplon.Prod
 */

namespace simplonprod\newslettertests\unit\controllers;

use Codeception\Stub;
use Codeception\Stub\Expected;
use Craft;
use craft\web\Response;
use simplonprod\newsletter\Newsletter;
use simplonprod\newslettertests\unit\BaseUnitTest;

/**
 * RegisterTest class
 *
 * @author albanjubert
 **/
class NewsletterTest extends BaseUnitTest
{
    public function testSubscribeSuccess()
    {
        $componentInstance = \Craft::$app->get('urlManager');
        \Craft::$app->set('urlManager', Stub::construct(get_class($componentInstance), [], [
            'setRouteParams' => Expected::never()
        ], $this));

        /** @var Response $result */
        $result = $this->runActionWithParams('newsletter/subscribe', [
            'consent' => true,
            'email'   => 'some@email.com',
        ]);

        $this->assertInstanceOf("\craft\web\Response", $result);
        $this->assertEquals(302, $result->statusCode);
    }

    public function testSubscribeFailOnMissingConsent()
    {
        $componentInstance = \Craft::$app->get('urlManager');
        \Craft::$app->set('urlManager', Stub::construct(get_class($componentInstance), [], [
            'setRouteParams' => Expected::once()
        ], $this));

        /** @var Response $result */
        $result = $this->runActionWithParams('newsletter/subscribe', [
            'consent' => false,
            'email'   => 'some@email.com',
        ]);

        $this->assertNull($result);
    }

    public function testSubscribeFailOnInvalidEmail()
    {
        $componentInstance = \Craft::$app->get('urlManager');
        \Craft::$app->set('urlManager', Stub::construct(get_class($componentInstance), [], [
            'setRouteParams' => Expected::once()
        ], $this));

        /** @var Response $result */
        $result = $this->runActionWithParams('newsletter/subscribe', [
            'consent' => true,
            'email'   => 'some@email',
        ]);

        $this->assertNull($result);
    }

    protected function _before()
    {
        parent::_before();

        $this->tester->mockMethods(
            Newsletter::$plugin,
            'adapter',
            [
                'subscribe'            => function (string $email) {
                    return true;
                },
                'getSubscriptionError' => function () {
                    return "Some error";
                }
            ]
        );

        // Set controller namespace to web
        Newsletter::$plugin->controllerNamespace = str_replace('\\console', '', Newsletter::$plugin->controllerNamespace);

        // Force post request
        $_POST[Craft::$app->request->methodParam] = 'post';

        // Disable CSRF validation
        Craft::$app->request->enableCsrfValidation = false;
    }
}
