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
        $sql = "SELECT s.*,
                    sdp.title AS sdp_title,
                    sdp.short_description AS sdp_short_description,
                    sdp.long_description AS sdp_long_description,
                    sdp.location_name AS sdp_location_name,
                    sdp.location_lat AS sdp_location_lat,
                    sdp.location_lng AS sdp_location_lng,
                    sdp.link AS sdp_link,
                    sda.title AS sda_title,
                    sda.short_description AS sda_short_description,
                    sda.long_description AS sda_long_description,
                    sda.location_name AS sda_location_name,
                    sda.location_lat AS sda_location_lat,
                    sda.location_lng AS sda_location_lng,
                    sda.link AS sda_link
                FROM sessions s
                INNER JOIN session_details sdp on s.proposed_details = sdp.id
                LEFT JOIN session_details sda on s.accepted_details = sda.id
                WHERE owner = :ownerid";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('ownerid', $ownerId);
        $stmt->execute();

        $result = array_map([Session::class, 'fromRecord'], $stmt->fetchAll());

        return $result;
    }
}