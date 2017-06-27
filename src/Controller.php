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
    const ENDPOINT = null;

    /** @var array */
    const ROUTES = [ // @codingStandardsIgnoreStart
        // because {id} is added later, any static route MUST be placed
        // before routes with arguments
        ['action' => 'create',  'methods' => ['post'],         'route' => '/'],
        ['action' => 'index',   'methods' => ['get'],          'route' => '/'],
        ['action' => 'new',     'methods' => ['get'],          'route' => '/new'],

        // route with arguments
        ['action' => 'destroy', 'methods' => ['delete'],       'route' => '/{id}'],
        ['action' => 'edit',    'methods' => ['get'],          'route' => '/{id}/edit'],
        ['action' => 'get',     'methods' => ['get'],          'route' => '/{id}'],
        ['action' => 'update',  'methods' => ['patch', 'put'], 'route' => '/{id}'],
    ]; // @codingStandardsIgnoreEnd

    /** @var string */
    const SUFFIX = 'Action';

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
    public function __construct(ContainerInterface $container)
    {
        /* @var Interop\Container\ContainerInterface $container */
        $this->container = $container;

        $this->init();
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
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args) // @codingStandardsIgnoreLine
    {
        /** @var string $route */
        $route = $request->getAttribute('route')->getName();

        /** @var string $action */
        $action = $route ?: $request->getMethod();

        $this->action = strtolower($action);

        /** @var string $callback */
        $callback = $this->getCallbackName();

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
    public static function bootstrap(App $app)
    {
        return self::$app = $app;
    }

    /**
     * Map all given $controller within the $app.
     *
     * @param string $controller controllers to map
     */
    public static function map(/* string */ ...$controller)
    {
        if (!self::$app) {
            throw new RuntimeException(
                'You must init the `Slim\App` using `DL2\Slim\Controller::init(Slim\App)`' // @codingStandardsIgnoreLine
            );
        }

        while (list(, $class) = each($controller)) {
            $class::route();
        }
    }

    /**
     * Format the action callback name.
     *
     * @return string
     */
    protected function getCallbackName()
    {
        return preg_replace('/.+\./', '', $this->action) . static::SUFFIX;
    }

    /**
     * Returns the $app used by the controller container.
     *
     * @return Slim\App
     */
    protected function app()
    {
        return self::$app;
    }

    /**
     * Subclasses can override this to do any additional setup work that
     * would be considered part of `{@link __construct}`.
     *
     * Essentially, it is a hook into the parent constructor before the
     * controller is initialized.
     */
    protected function init()
    {
    }

    /**
     * Output the rendered template and returns the current
     * ResponseInterface.
     *
     * @param array $data associative array of template variables
     * @param string $template template pathname relative to
     *      templates directory
     *
     * @throws Slim\Exception\ContainerValueNotFoundException if the
     *      renderer is not defined
     *
     * @return Psr\Http\Message\ResponseInterface
     */
    protected function render(array $data = [], /*string*/ $template = '')
    {
        /** @var Slim\Views\PhpRenderer $renderer */
        $renderer = $this->container->get('renderer');

        /** @var Psr\Http\Message\ResponseInterface $response */
        $response = $this->container->get('response');

        if (!$template) {
            /** @var string $template */
            $template = str_replace('.', '/', $this->action) . '.phtml';
        }

        return $renderer->render($response, $template, $data);
    }

    /**
     * Map all routes which would use this Controller as handler.
     */
    protected static function route()
    {
        /** @var Slim\App $app */
        $app = self::$app;

        /** @var string $controller */
        $controller = strtolower(str_replace(['\\'], ['/'], static::class));

        /** @var string $endpoint */
        $endpoint = trim(static::ENDPOINT ?: $controller, '/');
        $endpoint = preg_replace('@^(controllers?|modules?)/@', '', $endpoint);

        // calls `__invoke` as the unique handler
        if (!static::ROUTES) {
            return $app->any("/{$endpoint}[/{id}]", static::class);
        }

        foreach (static::ROUTES as /* array */ $mapping) {
            /** @var array $methods */
            $methods = $mapping['methods'];

            /** @var string $route */
            $route = rtrim("/{$endpoint}{$mapping['route']}", '/') ?: '/';
            $route = preg_replace('@/+@', '/', $route);

            /** @var Slim\Route $mapper */
            $mapper = $app->map($methods, $route, static::class);

            if (isset($mapping['action']) && $mapping['action']) {
                /** @var string $controller */
                $controller = preg_replace('@.+/@', '', $controller);

                $mapper->setName("{$controller}.{$mapping['action']}");
            }
        }
    }
}
