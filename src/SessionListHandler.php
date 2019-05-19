<?php


namespace App;


use Doctrine\DBAL\Connection;
use TopicAdvisor\Lambda\RuntimeApi\Http\HttpRequestInterface;
use TopicAdvisor\Lambda\RuntimeApi\Http\HttpResponse;
use TopicAdvisor\Lambda\RuntimeApi\InvocationRequestHandlerInterface;
use TopicAdvisor\Lambda\RuntimeApi\InvocationRequestInterface;
use TopicAdvisor\Lambda\RuntimeApi\InvocationResponseInterface;

class SessionListHandler implements InvocationRequestHandlerInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param InvocationRequestInterface $request
     * @return bool
     */
    public function canHandle(InvocationRequestInterface $request): bool
    {
        return $request instanceof HttpRequestInterface;
    }

    /**
     * @param InvocationRequestInterface $request
     * @return void
     */
    public function preHandle(InvocationRequestInterface $request)
    {
    }

    /**
     * @param InvocationRequestInterface $request
     * @return InvocationResponseInterface
     * @throws \Exception
     */
    public function handle(InvocationRequestInterface $request): InvocationResponseInterface
    {
        if (!$request instanceof HttpRequestInterface) {
            throw new \LogicException('Must be invoked with HttpRequestInterface only');
        }

        $response = new HttpResponse($request->getInvocationId());

        if ($request->getMethod() !== 'GET') {
            $response->setStatusCode(405);
            return $response;
        }

        $userId = $this->extractUserId($request);


        $sql = "SELECT * FROM sessions WHERE owner = :userid";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('userid', $userId);
        $stmt->execute();

        $result = $stmt->fetchAll();

        $response = new HttpResponse($request->getInvocationId());
        $response->setStatusCode(200);
        $response->setHeaders(['Content-type' => 'application/json']);
        $response->setBody(\json_encode(['sessions' => $result]));

        return $response;
    }

    /**
     * @param InvocationRequestInterface $request
     * @param InvocationResponseInterface $response
     * @return void
     */
    public function postHandle(InvocationRequestInterface $request, InvocationResponseInterface $response)
    {
    }

    private function extractUserId(HttpRequestInterface $request): int
    {
        $authHeaderParts = \explode(' ', $request->getHeaderLine('Authorization'), 2);

        if (\count($authHeaderParts) !== 2 || $authHeaderParts[0] !== 'Bearer') {
            throw new \RuntimeException('Invalid Authorization');
        }

        $jwtParts = \explode('.', $authHeaderParts[1]);

        if (\count($jwtParts) !== 3) {
            throw new \RuntimeException('Malformed JWT Token');
        }

        $decodedPayload = \base64_decode($jwtParts[1]);

        if ($decodedPayload === false) {
            throw new \RuntimeException('Failed to base64-decode payload data. Invalid JWT token.');
        }

        $jwtPayload = \json_decode($decodedPayload, true);

        if (!is_array($jwtPayload) || !isset($jwtPayload['sub']) || !is_string($jwtPayload['sub'])) {
            throw new \RuntimeException('Malformed JWT Subject Record');
        }

        return (int)$jwtPayload['sub'];
    }
}
