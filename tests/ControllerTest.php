<?php
/**
 * https://dl2.tech - DL2 IT Services
 * Owlsome solutions. Owltstanding results.
 */

namespace DL2\Slim\Tests;

require_once __DIR__ . '/../example/controllers/Example.php';

use DL2\Slim\Controller;
use PHPUnit\Framework\TestCase;
use Slim\App;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * @codingStandardsIgnoreStart
 *
 * because we declare 2 classes in the same
 * file: PSR1.Classes.ClassDeclaration.MultipleClasses
 */
class ExampleExt extends \Example
{
    const endpoint = '/';
}

class ControllerTest extends TestCase
{
    // @codingStandardsIgnoreEnd

    /**
     * Process the application given a request method and URI.
     *
     * @param string $method the request method (e.g. GET, POST, etc.)
     * @param string $uri the request URI
     * @param array|object|null $body the request data
     *
     * @return Slim\Http\Response
     */
    public function runApp($method, $uri, $body = null)
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
        $app = Controller::init(new App());

        // map routes in `Example`
        Controller::map('Example');

        // map routes in `ExampleExt`
        Controller::map('DL2\Slim\Tests\ExampleExt');

        // Process the application
        $response = $app->process($request, $response);

        // Return the response
        return $response;
    }

    /**
     * Test that the controller returns the correct response with named
     * endpoints.
     */
    public function testIndexExt()
    {
        $response = $this->runApp('POST', '/', ['a', 'b']);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertContains('application/json', $response->getHeader('content-type')[0]);
        $this->assertEquals('["a","b"]', (string) $response->getBody());
    }

    /**
     * Test that the controller returns a rendered response containing the
     * text 'DL2 / Slim Controller' but not a JSON string.
     */
    public function testIndex()
    {
        $response = $this->runApp('GET', '/example', null);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('DL2 / Slim Controller', (string) $response->getBody());
    }

    /**
     * Test that the controller returns a 404 when a trailing slash is added.
     */
    public function testIndexWithSlash()
    {
        $response = $this->runApp('GET', '/example/', null);

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertContains('Page Not Found', (string) $response->getBody());
    }

    /**
     * Test that the controller with optional name argument returns a
     * rendered JSON string read from the body.
     */
    public function testGet()
    {
        $response = $this->runApp('GET', '/example/1337', null);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('application/json', $response->getHeader('content-type')[0]);
        $this->assertEquals('{"id":"1337"}', (string) $response->getBody());
    }

    /**
     * Test that the controller won't accept a `DELETE` request.
     */
    public function testDeleteNotAllowed()
    {
        $response = $this->runApp('DELETE', '/example');

        $this->assertEquals(405, $response->getStatusCode());
        $this->assertEquals('Method Not Allowed', $response->getReasonPhrase());
    }
}
