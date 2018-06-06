<?php

namespace App\ValueObject;

class BoardGame
{
    /** @var int */
    private $id;

    /** @var string */
    private $name;

    /** @var string */
    private $description;

    /** @var null|string */
    private $image;

    /**
     * @param int $id
     * @param string $name
     * @param string $description
     * @param null|string $image
     */
    public function __construct(
        int $id,
        string $name,
        string $description,
        ?string $image
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->image = $image;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return null|string
     */
    public function getImage(): ?string
    {
        return $this->image;
    }
}