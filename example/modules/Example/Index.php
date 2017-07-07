<?php
/**
 * https://dl2.tech - DL2 IT Services
 * Owlsome solutions. Owltstanding results.
 */

namespace Modules\Example;

use DL2\Slim\Controller;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\NotFoundException;
use Slim\Views\PhpRenderer;
use stdClass;

/**
 * Simple rest example. Note that `DELETE` is not allowed.
 */
class Index extends Controller
{
    const ENDPOINT = '/example';
    /**
     * Fake DB data.
     *
     * @var stdClass[]
     */
    private $database;

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
     * @param Psr\Http\Message\ServerRequestInterface $request
     * @param Psr\Http\Message\ResponseInterface $response
     * @param array $args
     *
     * @return Psr\Http\Message\ResponseInterface
     */
    public function destroyAction(ServerRequestInterface $request, ResponseInterface $response, array $args) // @codingStandardsIgnoreLine
    {
        /** @var stdClass $record */
        $record = $this->findRecordInDatabase($args['id']);

        return $response->withJson($record, 204);
    }

    /**
     * @param Psr\Http\Message\ServerRequestInterface $request
     * @param Psr\Http\Message\ResponseInterface $response
     * @param array $args
     *
     * @return Psr\Http\Message\ResponseInterface
     */
    public function editAction(ServerRequestInterface $request, ResponseInterface $response, array $args) // @codingStandardsIgnoreLine
    {
        /** @var stdClass $record */
        $record = $this->findRecordInDatabase($args['id']);

        return $response->withJson($record);
    }

    /**
     * @param Psr\Http\Message\ServerRequestInterface $request
     * @param Psr\Http\Message\ResponseInterface $response
     * @param array $args
     *
     * @return Psr\Http\Message\ResponseInterface
     */
    public function getAction(ServerRequestInterface $request, ResponseInterface $response, array $args) // @codingStandardsIgnoreLine
    {
        /** @var stdClass $record */
        $record = $this->findRecordInDatabase($args['id']);

        return $response->withJson($record);
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
        /** @var string $path */
        $path = realpath(__DIR__ . '/templates');

        /* @var Slim\Views\PhpRenderer $renderer */
        $this->container['renderer'] = new PhpRenderer($path);

        return $this->render([
            'data'  => $this->database,
        ]);
    }

    /**
     * @param Psr\Http\Message\ServerRequestInterface $request
     * @param Psr\Http\Message\ResponseInterface $response
     * @param array $args
     *
     * @return Psr\Http\Message\ResponseInterface
     */
    public function newAction(ServerRequestInterface $request, ResponseInterface $response) // @codingStandardsIgnoreLine
    {
        return $response->withJson([]);
    }

    /**
     * @param Psr\Http\Message\ServerRequestInterface $request
     * @param Psr\Http\Message\ResponseInterface $response
     * @param array $args
     *
     * @return Psr\Http\Message\ResponseInterface
     */
    public function updateAction(ServerRequestInterface $request, ResponseInterface $response, array $args) // @codingStandardsIgnoreLine
    {
        /** @var stdClass $record */
        $record = $this->findRecordInDatabase($args['id']);

        return $response->withJson($record);
    }

    /**
     * Setup up a fake DB.
     */
    protected function init()
    {
        /** @var stdClass[] $database */
        $database = json_decode(file_get_contents(__DIR__ . '/../data.json'));

        foreach ($database as /* stdClass */ $record) {
            $this->database[$record->login->salt] = $record;
        }

        // used in our tests
        $this->database['exists'] = $record;
    }

    /**
     * find a record in our DB.
     */
    protected function findRecordInDatabase(string $inputId): stdClass
    {
        if (!isset($this->database[$inputId])) {
            // in s real app, this record would not exists in our DB
            throw new NotFoundException(
                $this->container->request,
                $this->container->response
            );
        }

        return $this->database[$inputId];
    }
}
