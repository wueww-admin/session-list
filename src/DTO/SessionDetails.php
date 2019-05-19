<?php


namespace App\DTO;


class SessionDetails implements \JsonSerializable
{
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

    public function jsonSerialize()
    {
        return [
            'title' => $this->record['title'],
            'description' => [
                'short' => $this->record['short_description'],
                'long' => $this->record['long_description'],
            ],
            'location' => [
                'name' => $this->record['location_name'],
                'lat' => $this->record['location_lat'],
                'lng' => $this->record['location_lng'],
            ],
            'link' => $this->record['link'],
        ];
    }
}