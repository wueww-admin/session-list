<?php


namespace App;


use App\DTO\Session;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;

class SessionRepository
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
     * @param int $ownerId
     * @return array
     * @throws DBALException
     */
    public function findByOwner(int $ownerId): array
    {
        $sql = "SELECT * FROM sessions WHERE owner = :ownerid";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('ownerid', $ownerId);
        $stmt->execute();

        $result = array_map([Session::class, 'fromRecord'], $stmt->fetchAll());

        return $result;
    }
}