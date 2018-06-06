<?php

namespace App\Command;

class GetBGGData
{
    /** @var int */
    private $id;

    /** @var bool */
    private $resizeImage;

    /** @var bool */
    private $exportJson;

    /** @var bool */
    private $exportXml;

    /**
     * @param int $id
     * @param bool $resizeImage
     * @param bool $exportJson
     * @param bool $exportXml
     */
    public function __construct(int $id, bool $resizeImage, bool $exportJson, bool $exportXml)
    {
        $this->id = $id;
        $this->resizeImage = $resizeImage;
        $this->exportJson = $exportJson;
        $this->exportXml = $exportXml;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function isResizeImage(): bool
    {
        return $this->resizeImage;
    }

    /**
     * @return bool
     */
    public function isExportJson(): bool
    {
        return $this->exportJson;
    }

    /**
     * @return bool
     */
    public function isExportXml(): bool
    {
        return $this->exportXml;
    }
}