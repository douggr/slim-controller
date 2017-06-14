<?php
/**
 * https://dl2.tech - DL2 IT Services
 * Owlsome solutions. Owltstanding results.
 */

namespace DL2\Slim;

use Interop\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Slim\App;
use Slim\Exception\MethodNotAllowedException;
use Slim\Exception\NotFoundException;

/**
 * Action Controller extension for the Slim Framework.
 *
 * An abstract class you may use for implementing Action Controllers
 * to use within `Slim\App` routes.
 */
abstract class Controller
{
    /** @var string */
    const endpoint = null;

    /** @var array */
    const routes = [
        ['action' => 'create',  'methods' => ['post'],         'route' => '/'],
        ['action' => 'destroy', 'methods' => ['delete'],       'route' => '/{id:[0-9]+}'],
        ['action' => 'edit',    'methods' => ['get'],          'route' => '/{id:[0-9]+}/edit'],
        ['action' => 'get',     'methods' => ['get'],          'route' => '/{id:[0-9]+}'],
        ['action' => 'index',   'methods' => ['get'],          'route' => '/'],
        ['action' => 'new',     'methods' => ['get'],          'route' => '/new'],
        ['action' => 'update',  'methods' => ['patch', 'put'], 'route' => '/{id:[0-9]+}'],
    ];

    /**
     * @var string
     */
    protected $action;

    /**
     * @var Interop\Container\ContainerInterface
     */
    protected $container;

    /**
     * @var Slim\App
     */
    private static $app;

    /**
     * Ctor. Receives a `ContainerInterface` instance.
     *
     * @param Interop\Container\ContainerInterface $container
     *
     * @internal
     */
    final public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param Psr\Http\Message\ServerRequestInterface $request
     * @param Psr\Http\Message\ResponseInterface $response
     * @param array $args
     *
     * @return Psr\Http\Message\ResponseInterface
     *
     * @internal
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        /** @var string $action */
        $action         = $request->getAttribute('route')->getName() ?: $request->getMethod();
        $this->action   = strtolower($action);

        /** @var string $callback */
        $callback = preg_replace('/.+\./', '', $this->action);

        if (method_exists($this, $callback)) {
            return call_user_func_array([$this, $callback], [
                /* 0: ServerRequestInterface    */ $request,
                /* 1: ResponseInterface         */ $response,
                /* 2: array                     */ $args,
            ]);
        }

        if ('GET' === $request->getMethod()) {
            throw new NotFoundException($request, $response);
        }

        throw new MethodNotAllowedException($request, $response, ['GET']);
    }

    /**
     * Configure a Slim\App to use within this controller.
     *
     * @param Slim\App
     *
     * @return Slim\App
     */
    final public static function init(App $app)
    {
        return self::$app = $app;
    }

    /**
     * Map all given $controller within the $app.
     *
     * @param string $controller controllers to map
     */
    final public function map(/* string */ ...$controller)
    {
        if (!self::$app) {
            throw new RuntimeException(
                'You must init the `Slim\App` using `DL2\Slim\Controller::init(Slim\App)`'
            );
        }

        while (list(, $class) = each($controller)) {
            $class::route();
        }
    }

    /**
     * Returns the $app used by the controller container.
     *
     * @return Slim\App
     */
    final protected function app()
    {
        return self::$app;
    }

    /**
     * Output the rendered template and returns the current ResponseInterface.
     *
     * @param array $data associative array of template variables
     * @param string $template template pathname relative to templates directory
     *
     * @throws Slim\Exception\ContainerValueNotFoundException if the renderer
     *      is not defined
     *
     * @return Psr\Http\Message\ResponseInterface
     */
    protected function render(array $data = [], /*string*/ $template = '')
    {
        /** @var Slim\Views\PhpRenderer $renderer */
        $renderer = $this->container->get('renderer');

        if (!$template) {
            /** @var string $template */
            $template = str_replace('.', '/', $this->action) . '.phtml';
        }

        return $renderer->render($this->container->get('response'), $template, $data);
    }

    /**
     * Forward to another action.
     *
     * @param string $action action to forward to
     * @param array $args
     *
     * @return Psr\Http\Message\ResponseInterface
     */
    protected function forward($action, array $args = [])
    {
        /** @var Slim\Http\Request $request */
        $request = $this->container->get('request');

        /** @var Slim\Http\Response $response */
        $response = $this->container->get('response');

        return call_user_func_array([$this, $this->action = $action], [
            /* 0: ServerRequestInterface    */ $request,
            /* 1: ResponseInterface         */ $response,
            /* 2: array                     */ $args,
        ]);
    }

    /**
     * Map all routes which would use this Controller as handler.
     */
    private static function route()
    {
        /** @var Slim\App $app */
        $app = self::$app;

        /** @var string $controller */
        $controller = strtolower(str_replace('\\', '/', static::class));

        /** @var string $endpoint */
        $endpoint = static::endpoint ?: $controller;

        // calls `__invoke` as the unique handler
        if (!static::routes) {
            return $app->any("/{$endpoint}[/{id}]", static::class);
        }

        foreach (static::routes as /* array */ $mapping) {
            /** @var array $methods */
            $methods = $mapping['methods'];

            /** @var string $route */
            $route = rtrim("/{$endpoint}{$mapping['route']}", '/');

            /** @var Slim\Route $mapper */
            $mapper = $app->map($methods, $route, static::class);

            if (isset($mapping['action']) && $mapping['action']) {
                $mapper->setName("{$controller}.{$mapping['action']}");
            }
        }
    }
}
