<?php
/**
 * https://dl2.tech - DL2 IT Services
 * Owlsome solutions. Owltstanding results.
 */

namespace DL2\Slim;

use Interop\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionClass;
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

    /** @var string */
    const RENDERER_EXTENSION = '.phtml';

    // @codingStandardsIgnoreStart
    /** @var array */
    const ROUTES = [
        // because {id} is added later, any static route MUST be
        // placed before routes with arguments
        ['action' => 'create',  'methods' => ['post'],         'route' => '/'],
        ['action' => 'index',   'methods' => ['get'],          'route' => '/'],
        ['action' => 'new',     'methods' => ['get'],          'route' => '/new'],

        // and then define dynamic routes
        ['action' => 'destroy', 'methods' => ['delete'],       'route' => '/{id}'],
        ['action' => 'edit',    'methods' => ['get'],          'route' => '/{id}/edit'],
        ['action' => 'get',     'methods' => ['get'],          'route' => '/{id}'],
        ['action' => 'update',  'methods' => ['patch', 'put'], 'route' => '/{id}'],
    ];
    // @codingStandardsIgnoreEnd

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
    final public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args) // @codingStandardsIgnoreLine
    {
        /** @var string $route */
        $route = $request->getAttribute('route')->getName();

        /* @var string $action */
        $this->action = basename(strtolower($route ?? $request->getMethod()));

        /** @var string $callback */
        $callback = "{$this->action}Action";

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
     * @param array $config an associative array of app settings as follow:
     *  - addContentLengthHeader: When true, Slim will add a
     *      Content-Length header to the response. If you are using a
     *      runtime analytics tool, such as New Relic, then this
     *      should be disabled.
     *  - bootstrap: A file to load right after the App is instantiated
     *  - determineRouteBeforeAppMiddleware: When true, the route is
     *      calculated before any middleware is executed. This means
     *      that you can inspect route parameters in middleware if you
     *      need to.
     *  - displayErrorDetails: When true, additional information about
     *      exceptions are displayed by the default error handler.
     *  - httpVersion: The protocol version used by the Response object.
     *  - responseChunkSize: Size of each chunk read from the `Response`
     *      body when sending to the browser.
     *  - outputBuffering: If false, then no output buffering is
     *      enabled. If 'append' or 'prepend', then any echo or print
     *      statements are captured and are either appended or prepended
     *      to the Response returned from the route callable.
     *  - routerCacheFile: Filename for caching the FastRoute routes.
     *      Must be set to to a valid filename within a writeable
     *      directory. If the file does not exist, then it is
     *      created with the correct cache information on first run.
     *
     * @return Slim\App
     */
    public static function bootstrap(array $config = []): App
    {
        if (self::$app) {
            return self::$app;
        }

        $config = array_replace([
            'addContentLengthHeader'            => true,
            'determineRouteBeforeAppMiddleware' => false,
            'displayErrorDetails'               => false,
            'httpVersion'                       => '1.1',
            'outputBuffering'                   => 'append',
            'responseChunkSize'                 => 4096,
            'routerCacheFile'                   => false,
        ], $config);

        /* @var Slim\App $app */
        return self::$app = new App(['settings' => $config]);
    }

    /**
     * Map all given $controller within the $app.
     *
     * @param string[] $controller controllers to map
     */
    public static function map(string ...$controller)
    {
        if (!self::$app) {
            throw new RuntimeException(
                'You must init the `Slim\App` using `DL2\Slim\Controller::bootstrap(array $config)`' // @codingStandardsIgnoreLine
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
    protected function app(): App
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
     * @param string $template template name. Relative to the
     *      templates directory (without file extension)
     *
     * @throws Slim\Exception\ContainerValueNotFoundException if the
     *      renderer is not defined
     *
     * @return Psr\Http\Message\ResponseInterface
     */
    protected function render(array $data = [], string $template = ''): ResponseInterface
    {
        if (!$template) {
            /** @var ReflectionClass $reflection */
            $reflection = new ReflectionClass(static::class);

            /** @var string $path */
            $path = $reflection->getShortName();

            /** @var string $template */
            $template = strtolower("{$path}/{$this->action}");
        }

        /** @var Slim\Views\PhpRenderer $renderer */
        $renderer = $this->container->get('renderer');

        /** @var Psr\Http\Message\ResponseInterface $response */
        $response = $this->container->get('response');

        /* @var string $template */
        $template .= static::RENDERER_EXTENSION;

        return $renderer->render($response, $template, $data);
    }

    /**
     * Map all routes which would use this Controller as handler.
     */
    protected static function route()
    {
        /** @var string $controller */
        $controller = strtolower(str_replace('\\', '/', static::class));

        /** @var string $endpoint */
        $endpoint = trim(static::ENDPOINT ?: $controller, '/');

        foreach (static::ROUTES as $mapping) {
            /** @var string $route */
            $route = trim("{$endpoint}{$mapping['route']}", '/');

            self::$app
                ->map($mapping['methods'], "/{$route}", static::class)
                ->setName("{$route}/{$mapping['action']}");
        }
    }
}
