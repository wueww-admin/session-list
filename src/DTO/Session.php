<?php


namespace App\DTO;


class Session implements \JsonSerializable
{
    const JSON_DATETIME_FORMAT = 'Y-m-d\TH:i:sP';

    /**
     * @var string[]
     */
    private $record;

    private function __construct(array $record)
    {
        $this->record = $record;
    }

    public static function fromRecord(array $data): self
    {
        return new self($data);
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return (int) $this->record['id'];
    }

    /**
     * @return \DateTimeImmutable
     * @throws \Exception
     */
    public function getStart(): \DateTimeImmutable
    {
        return new \DateTimeImmutable($this->record['start']);
    }

    /**
     * @return \DateTimeImmutable|null
     * @throws \Exception
     */
    public function getEnd()
    {
        return $this->record['end'] === null ? null : new \DateTimeImmutable($this->record['end']);
    }

    /**
     * @return bool
     */
    public function isCancelled(): bool
    {
        return $this->record['cancelled'];
    }

    /**
     * {@inheritDoc}
     * @throws \Exception
     */
    public function jsonSerialize()
    {
        $end = $this->getEnd();

        return [
            'id' => $this->getId(),
            'start' => $this->getStart()->format(self::JSON_DATETIME_FORMAT),
            'end' => $end === null ? null : $end->format(self::JSON_DATETIME_FORMAT),
            'cancelled' => $this->isCancelled(),
        ];
    }
}