<?php
/**
 * https://dl2.tech - DL2 IT Services
 * Owlsome solutions. Owltstanding results.
 */

namespace DL2\Slim\Tests;

require __DIR__ . '/../example/modules/Example/Index.php';

use DL2\Slim\Controller;
use Modules\Example\Index as Example;
use PHPUnit\Framework\TestCase;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

class ControllerTest extends TestCase
{
    /**
     * Test that the controller returns the correct response
     * on POST requests.
     */
    public function testCreateAction()
    {
        /** @var Slim\Http\Response $response */
        $response = $this->runApp('POST', '/example', ['a', 'b']);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('["a","b"]', (string) $response->getBody());
    }

    /**
     * Test that the controller returns the correct response
     * on DELETE requests.
     */
    public function testDestroyAction()
    {
        /** @var Slim\Http\Response $response */
        $response = $this->runApp('DELETE', '/example/exists');
        $this->assertEquals(204, $response->getStatusCode());

        /** @var Slim\Http\Response $response */
        $response = $this->runApp('DELETE', '/example/not-found');
        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * Test that the controller returns the correct response
     * on GET requests with more than one argument.
     */
    public function testEditAction()
    {
        /** @var Slim\Http\Response $response */
        $response = $this->runApp('GET', '/example/exists/edit');
        $this->assertEquals(200, $response->getStatusCode());

        /** @var Slim\Http\Response $response */
        $response = $this->runApp('GET', '/example/not-found/edit');
        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * Test that the controller returns the correct response
     * on GET requests with a single dynamic arguments.
     */
    public function testGetAction()
    {
        /** @var Slim\Http\Response $response */
        $response = $this->runApp('GET', '/example/exists');
        $this->assertEquals(200, $response->getStatusCode());

        /** @var Slim\Http\Response $response */
        $response = $this->runApp('GET', '/example/not-found');
        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * Test that the controller returns the correct response
     * on GET requests with no args.
     */
    public function testIndexAction()
    {
        /** @var Slim\Http\Response $response */
        $response = $this->runApp('GET', '/example');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('DL2 / Slim Controller', (string) $response->getBody());
    }

    /**
     * Test that the controller returns the correct response
     * on GET requests with static args.
     */
    public function testNewAction()
    {
        /** @var Slim\Http\Response $response */
        $response = $this->runApp('GET', '/example/new');
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test that the controller returns the correct response
     * on PATCH and PUT requests.
     */
    public function testUpdateAction()
    {
        /** @var Slim\Http\Response $response */
        $response = $this->runApp('PATCH', '/example/exists');
        $this->assertEquals(200, $response->getStatusCode());

        /** @var Slim\Http\Response $response */
        $response = $this->runApp('PUT', '/example/exists');
        $this->assertEquals(200, $response->getStatusCode());

        /** @var Slim\Http\Response $response */
        $response = $this->runApp('PATCH', '/example/not-found');
        $this->assertEquals(404, $response->getStatusCode());

        /** @var Slim\Http\Response $response */
        $response = $this->runApp('PUT', '/example/not-found');
        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * Process the application given a request method and URI.
     *
     * @param string $method the request method (e.g. GET, POST, etc.)
     * @param string $uri the request URI
     * @param array|object|null $body the request data
     *
     * @return Slim\Http\Response
     */
    protected function runApp($method, $uri, $body = null)
    {
        // Create a mock environment for testing with
        $environment = Environment::mock(
            [
                'REQUEST_METHOD' => strtoupper($method),
                'REQUEST_URI'    => $uri,
            ]
        );

        // Set up a request object based on the environment
        $request = Request::createFromEnvironment($environment);

        // Add request data, if it exists
        if ($body) {
            $request = $request->withParsedBody($body);
        }

        // Set up a response object
        $response = new Response();

        // Instantiate the application
        $app = Controller::bootstrap();

        // map routes in `Example`
        Controller::map(Example::class);

        // Process the application
        $response = $app->process($request, $response);

        // Return the response
        return $response;
    }
}
