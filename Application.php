<?php

namespace rkay\rkaymvc;

use app\models\User;
use rkay\rkaymvc\db\Database;

class Application
{

    const EVENT_BEFORE_REQUEST = 'beforeRequest';
    const EVENT_AFTER_REQUEST = 'afterRequest';

    protected array $eventListners = [];

    public string $layout = 'main';

    public string $userClass;
    public Router $router;
    public Request $request;

    public Response $response;
    public Database $db;

    public Session $session;

    public static string $ROOT_DIR;
    public static Application $app;
    public ?Controller $controller = null;
    public ?UserModel $user;
    public View $view;

    public function __construct($rootpath, array $config)
    {
        $this->userClass = $config['userClass'];
        self::$ROOT_DIR = $rootpath;
        self::$app = $this;
        $this->request = new Request();
        $this->response = new Response();
        $this->router = new Router($this->request, $this->response);
        $this->db = new Database($config['db']);
        $this->view = new View();
        $this->session = new Session();
        $primaryValue = $this->session->get('user');
        if ($primaryValue) {
            /** @var User $user */
            $user = new $this->userClass;
            $primaryKey = $user->primarykey();
            $this->user = $user->findOne([$primaryKey => $primaryValue]);
        } else {
            $this->user = null;
        }
    }

    public static function isGest()
    {
        return !self::$app->user;
    }

    public function run()
    {
        $this->triggerEvent(self::EVENT_BEFORE_REQUEST);
        try {
            echo $this->router->resolve();
        } catch (\Exception $e) {
            $this->response->setStatusCode($e->getCode());
            echo $this->view->renderView('_error', [
                'exception' => $e
            ]);
        }
    }

    /**
     * @return Controller
     */
    public function getController(): Controller
    {
        return $this->controller;
    }

    /**
     * @param Controller $controller
     */
    public function setController(Controller $controller): void
    {
        $this->controller = $controller;
    }

    public function login(UserModel $user)
    {
        $this->user = $user;
        $primaryKey = $user->primaryKey();
        $primaryValue = $user->{$primaryKey};

        $this->session->set('user', $primaryValue);
        return true;
    }

    public function logout()
    {
        $this->user = null;
        $this->session->remove('user');
    }

    public function triggerEvent($eventName)
    {
        $callbacks = $this->eventListners[$eventName] ?? [];
        foreach ($callbacks as $callback) {
            call_user_func($callback);
        }
    }

    public function on($eventName, $callback)
    {
        $this->eventListners[$eventName][] = $callback;
    }

}
