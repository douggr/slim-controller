<?php
/**
 * https://dl2.tech - DL2 IT Services
 * Owlsome solutions. Owltstanding results.
 */

namespace Controllers;

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
     * Fake DB data.
     *
     * @var stdClass[]
     */
    private $db;

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
    public function getAction(ServerRequestInterface $request, ResponseInterface $response, array $args) // @codingStandardsIgnoreLine
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
    public function indexAction(ServerRequestInterface $request, ResponseInterface $response, array $args) // @codingStandardsIgnoreLine
    {
        if (null !== $request->getParam('json')) {
            return $response->withJson($this->db);
        }

        /** @var string $path */
        $path = realpath(__DIR__ . '/../templates');

        /* @var Slim\Views\PhpRenderer $renderer */
        $this->container['renderer'] = new PhpRenderer($path);

        return $this->render([
            'data'  => $this->db,
        ]);
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
    public function createAction(ServerRequestInterface $request, ResponseInterface $response, array $args) // @codingStandardsIgnoreLine
    {
        return $response->withJson($request->getParsedBody(), 201);
    }

    /**
     * Setup up a fake DB.
     */
    protected function init()
    {
        /** @var string $data */
        $data = file_get_contents(__DIR__ . '/data.json');

        /* @var stdClass $db */
        $this->db = json_decode($data)->data;
    }
}
