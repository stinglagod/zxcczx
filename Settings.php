<?php

namespace rent\settings;

use rent\cart\Cart;
use rent\cart\cost\calculator\DynamicCost;
use rent\cart\cost\calculator\SimpleCost;
use rent\cart\storage\HybridStorage;
use rent\entities\Client\Client;
use rent\entities\Client\Site;
use rent\entities\Client\Site\ReCaptcha;
use rent\entities\User\User;
use rent\helpers\AppHelper;
use rent\repositories\Client\ClientRepository;
use rent\repositories\Client\SiteRepository;
use rent\repositories\UserRepository;
use rent\settings\storage\StorageInterface;
use Yii;
use yii\base\Component;
use yii\caching\Cache;
use yii\caching\CacheInterface;
use yii\caching\TagDependency;

/**
 * @property Site $site
 * @property Client $client
 * @property User $user
 **/

class Settings extends Component
{
    public ?Site $site=null;
    public ?User $user;
    public ?Client $client=null;
    public ?ReCaptcha $reCaptcha=null;

    public $useSaveToSessionCache;

    public $cart;
    private $cache;
    private $repo_sites;
    private $repo_users;
    private $repo_clients;
    private $storage;
    private bool $isBackend=false;

    private $client_id;
    private $site_id;


    /**
     * Логика следующая. Если домен общий, тогда мы можем выбирать любые другие сайты
     * Если домен клиента, тогда выбор среди доменов этого клиента
     * @param Cache $cache
     * @param SiteRepository $repo_sites
     * @param UserRepository $repo_users
     * @param ClientRepository $repo_clients
     * @param Cart|null $cart
     * @param StorageInterface $storage
     * @param array $config
     */
    public function __construct(
        Cache $cache,
        SiteRepository $repo_sites,
        UserRepository $repo_users,
        ClientRepository $repo_clients,
        Cart $cart=null,
        StorageInterface $storage,
        bool $isBackend=false,
        $config = []
    )
    {
        $this->cache = $cache;
        $this->repo_sites = $repo_sites;
        $this->repo_users = $repo_users;
        $this->repo_clients = $repo_clients;
        $this->cart = $cart;
        $this->storage = $storage;
        $this->isBackend = $isBackend;

        $this->init();
        $this->initTimezone();

        if ($this->site) {
            $this->reCaptcha=$this->site->reCaptcha;
        } else {
            $site=$this->getSiteWithCache(Yii::$app->params['mainSiteId']);
            $this->reCaptcha=$site->reCaptcha;
        }

        parent::__construct($config);
    }

    public function initSite($domainOrId)
    {
        if (empty($this->client)) {
            throw new \DomainException('Не выбран клиент');
        }
        $this->site=$this->getSiteWithCache($domainOrId);
        $this->save();
    }

    /**
     *  Инициализируем клиента. Только для тестироания
     * @param int $clientId
     */
    public function initClient(int $clientId)
    {
        $this->client = $this->repo_clients->get($clientId);
        $this->site =$this->client->defaultSite;
        $this->save();
    }

    public function initTimezone(string $timezone=null)
    {
        if ($timezone) {
            date_default_timezone_set($timezone);
        } elseif (($this->client)and(($this->client->timezone))) {
            date_default_timezone_set($this->client->timezone);
        }
        \Yii::$app->params['dateControlDisplayTimezone']=date_default_timezone_get();
    }


    public function load()
    {
        $settings=$this->storage->load();

        $this->client_id=$settings->client_id;
        $this->site_id=$settings->site_id;


    }
    public function save()
    {
        if (AppHelper::isConsole()) return;
        \Yii::$app->session->set('settings_client_id',$this->client->id);
        if ($this->site) {
            \Yii::$app->session->set('settings_site_id',$this->site->id);
        } else {
            \Yii::$app->session->set('settings_site_id','');
        }

    }

    public function init()
    {
        parent::init();

        //инициализируем пользователя
        if ((empty(\Yii::$app->user))or(\Yii::$app->user->isGuest)) {
            $this->user=null;
        } else {
            $this->user=$this->cache->getOrSet(['settings_user', \Yii::$app->user->id], function () {
                return $this->repo_users->get(\Yii::$app->user->id);
            },Yii::$app->params['settingsCacheDuration'], new TagDependency(['tags' => ['users']]));
        }

        $loadSettings=$this->storage->load();

        //инициализируем сайт и клиента
        $currentSite=$this->getSiteWithCache($this->getDomainFromHost());
        //Если главный
        if ($currentSite->isMain()) {
//            $this->site=$currentSite;
            //Если админка, тогда по роли определяем клиента
            if ($this->isBackend()) {
                //Если гость то null
                if (\Yii::$app->user->isGuest) {
                    $this->client=null;
                    $this->site=null;
                } else {
                    //определяем роль
                    if (\Yii::$app->user->can('super_admin')) {
                        $this->site=$currentSite;
                        $this->client=$currentSite->client;

                        if ($loadSettings) {
                            if ($loadSettings->client_id) {
                                $this->client=$this->getClientWithCache($loadSettings->client_id);
                            }

                            if (($loadSettings->site_id)and($this->client->hasSite($loadSettings->site_id))) {

                                $this->site=$this->getSiteWithCache($loadSettings->site_id);
                            }
                        }

                    } else if (\Yii::$app->user->can('manager')) {
                        $this->client=$this->getClientWithCache($this->user->getClient()->id);
                        if ($site=$this->user->default_site) {
                            $this->site=$this->getSiteWithCache($site);
                        }

                    } else {
                        $this->client=null;
                        $this->site=null;
                    }
                }

            } else {
                //Если фронтенд, тогда чисто по клиенту
                $this->site=$currentSite;
                $this->client=$currentSite->client;
            }

        //Если клиентский сайт
        } else {
            //Если админка, тогда по роли определяем клиента
            if ($this->isBackend()) {
                if (\Yii::$app->user->isGuest) {
                    $this->client=null;
                    $this->site=null;
                } else {
                    //определяем роль
                    if (\Yii::$app->user->can('super_admin')) {
                        $this->site=$currentSite;
                        $this->client=$currentSite->client;
                    } else if (\Yii::$app->user->can('manager')) {
                        //проверяем может ли он открывать эту админку
                        if (($currentSite->client) and ($this->user->hasClient($currentSite->client->id))) {
                            $this->client=$this->getClientWithCache($this->user->default_client_id);
                            $this->site=$this->getSiteWithCache($this->user->default_site);
                        } else {
                            $this->client=null;
                            $this->site=null;
                        }

                    } else {
                        $this->client=null;
                        $this->site=null;
                    }
                }
            } else {
                $this->site=$currentSite;
                $this->client=$currentSite->client;
            }

        }
        if (!AppHelper::isConsole()) {
            //прописываем куку
            Yii::$app->user->identityCookie['domain']='.'.$this->getDomainFromHost();
            if (isset($this->site->is_https)) {
                Yii::$app->urlManager->setHostInfo('https://'.$this->getDomainFromHost());
            } else {
                Yii::$app->urlManager->setHostInfo('http://'.$this->getDomainFromHost());
            }

        }

    }
    public function isBackend():bool
    {
        return ((\Yii::$app->id=='app-backend') or ($this->isBackend));
    }
    public function getClientId():?int
    {
        if ($this->client) {
            return $this->client->id;
        }
        return null;
    }
    public function getClient():?Client
    {
        return $this->client;
    }

### Private
    private function getDomainFromHost():string
    {
        if (isset($_SERVER['HTTP_HOST'])) {
            return $_SERVER['HTTP_HOST'];
        } else {
            return \Yii::$app->params['siteDomain'];
//            throw new \DomainException('Invalid domain');
        }
    }

    private function getSiteWithCache($domainOrId):Site
    {
        return $this->cache->getOrSet(['settings_site', $domainOrId], function () use ($domainOrId)  {
            return $this->repo_sites->getByDomainOrId($domainOrId);
        }, Yii::$app->params['settingsCacheDuration'], new TagDependency(['tags' => ['sites','clients']]));
    }
    private function getClientWithCache(int $id):Client
    {
        return $this->cache->getOrSet(['settings_client', $id], function () use ($id)  {
                    return $this->repo_clients->get($id);
        }, Yii::$app->params['settingsCacheDuration'], new TagDependency(['tags' => ['clients']]));
    }
}