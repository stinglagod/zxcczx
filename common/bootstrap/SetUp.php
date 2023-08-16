<?php
/**
 * Created by PhpStorm.
 * User: Aleksey
 * Date: 27.05.2020
 * Time: 14:15
 */

namespace common\bootstrap;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use rent\cart\Cart;
use rent\cart\cost\calculator\DynamicCost;
use rent\cart\cost\calculator\SimpleCost;
use rent\cart\storage\HybridStorage;
use rent\repositories\events\EntityRemoved;
use rent\settings\Settings;
use rent\settings\storage\SimpleStorage;
use rent\settings\storage\StorageInterface;
use rent\useCases\ContactService;
use rent\dispatchers\AsyncEventDispatcher;
use rent\dispatchers\DeferredEventDispatcher;
use rent\dispatchers\EventDispatcher;
use rent\dispatchers\SimpleEventDispatcher;
use rent\jobs\AsyncEventJobHandler;
use rent\listeners\Shop\Category\CategoryPersistenceListener;
use rent\listeners\Shop\Product\ProductSearchPersistListener;
use rent\listeners\Shop\Product\ProductSearchRemoveListener;
use rent\repositories\events\EntityPersisted;
use Yii;
use yii\base\BootstrapInterface;
use yii\base\ErrorHandler;
use yii\di\Container;
use yii\di\Instance;
use yii\mail\MailerInterface;
use yii\caching\Cache;
use yii\queue\Queue;
use yii\rbac\ManagerInterface;

class SetUp implements BootstrapInterface
{
    public function bootstrap($app)
    {
        $container = \Yii::$container;

        //нужно получить siteId
        if (!$result=Yii::$app->db
            ->createCommand('SELECT * FROM client_sites WHERE domain=:domain')
            ->bindParam(':domain',$_SERVER['HTTP_HOST'])
            ->queryOne()) {
            $app->params['siteId']=1;
        } else {
            $app->params['siteId']=$result['id'];
        }



        $container->setSingleton(Client::class, function () {
            return ClientBuilder::create()->build();
        });

        $container->setSingleton(MailerInterface::class, function () use ($app) {
            return $app->mailer;
        });

        $container->setSingleton(ErrorHandler::class, function () use ($app) {
            return $app->errorHandler;
        });

        $container->setSingleton(Cache::class, function () use ($app) {
            return $app->cache;
        });

        $container->setSingleton(Cart::class, function () use ($app) {
            return new Cart(
                new HybridStorage($app->get('user'), $app->params['siteId'],'cart_'.$app->params['siteId'], 3600 * 24, $app->db),
                new DynamicCost(new SimpleCost())
            );
        });

        $container->setSingleton(StorageInterface::class, function () use ($app) {
            return new SimpleStorage();
        });

        $container->setSingleton(ManagerInterface::class, function () use ($app) {
            return $app->authManager;
        });

        $container->setSingleton(ContactService::class, [], [
            $app->params['adminEmail']
        ]);

        $container->setSingleton(Queue::class, function () use ($app) {
            return $app->get('queue');
        });

        $container->setSingleton(EventDispatcher::class, DeferredEventDispatcher::class);

        $container->setSingleton(DeferredEventDispatcher::class, function (Container $container) {
            return new DeferredEventDispatcher(new AsyncEventDispatcher($container->get(Queue::class)));
        });

        $container->setSingleton(SimpleEventDispatcher::class, function (Container $container) {
            return new SimpleEventDispatcher($container, [
//                UserSignUpRequested::class => [UserSignupRequestedListener::class],
//                UserSignUpConfirmed::class => [UserSignupConfirmedListener::class],
//                ProductAppearedInStock::class => [ProductAppearedInStockListener::class],
                EntityPersisted::class => [
                    ProductSearchPersistListener::class,
                    CategoryPersistenceListener::class,
//                    TestListener::class
                ],
                EntityRemoved::class => [
                    ProductSearchRemoveListener::class,
                    CategoryPersistenceListener::class,
                ],
            ]);
        });

        $container->setSingleton(AsyncEventJobHandler::class, [], [
            Instance::of(SimpleEventDispatcher::class)
        ]);

//        Yii::$app->settings;
    }

}