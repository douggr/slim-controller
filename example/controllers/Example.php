<?php
/**
 * https://dl2.tech - DL2 IT Services
 * Owlsome solutions. Owltstanding results.
 */

use DL2\Slim\Controller;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\PhpRenderer;

/**
 * Simple rest example. Note that `DELETE` is not allowed.
 */
class Example extends Controller
{
    /**
     * Used for retrieving resources. Handle `GET` requests
     * with an `ID` parameter.
     *
     * @param Psr\Http\Message\ServerRequestInterface $request
     * @param Psr\Http\Message\ResponseInterface $response
     * @param array $args
     *
     * @return Psr\Http\Message\ResponseInterface
     */
    public function get(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        return $response->withJson($args);
    }

    /**
     * Used for listing resources. Handle `GET` requests
     * without an `ID` parameter.
     *
     * @param Psr\Http\Message\ServerRequestInterface $request
     * @param Psr\Http\Message\ResponseInterface $response
     * @param array $args
     *
     * @return Psr\Http\Message\ResponseInterface
     */
    public function index(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $this->container['renderer'] = new PhpRenderer(__DIR__ . '/../templates');

        return $this->render();
    }

    /**
     * Used for creating resources. Handle `POST` requests.
     *
     * @param Psr\Http\Message\ServerRequestInterface $request
     * @param Psr\Http\Message\ResponseInterface $response
     * @param array $args
     *
     * @return Psr\Http\Message\ResponseInterface
     */
    public function post(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        return $response;
    }
}
