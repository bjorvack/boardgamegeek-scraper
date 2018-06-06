<?php

namespace App\Command;

use App\ValueObject\BoardGame;
use SimpleXMLElement;

class GetBGGDataHandler
{
    /** @var string */
    private $rootDir;

    /** @var string */
    private $bggEndpoint;

    /**
     * @param string $rootDir
     * @param string $bggEndpoint
     */
    public function __construct(
        string $rootDir,
        string $bggEndpoint
    ) {
        $this->rootDir = $rootDir;
        $this->bggEndpoint = $bggEndpoint;
    }

    /**
     * @param GetBGGData $data
     */
    public function handle(GetBGGData $data): void
    {
        $boardGame = $this->getData($data->getId());
    }

    /**
     * @param int $id
     *
     * @return BoardGame
     */
    private function getData(int $id): BoardGame
    {
        $data = $this->getCachedData($id);
        if (empty($data)) {
            $data = $this->getBoardGameGeekData($id);
        }

        return new BoardGame(
            $id,
            $data->children()[0]->name->attributes()['value'],
            $data->children()[0]->description,
            $data->children()[0]->image
        );
    }

    /**
     * @param int $id
     *
     * @return null|SimpleXMLElement
     */
    private function getCachedData(int $id): ?SimpleXMLElement
    {
        $cachedDataPath = $this->rootDir . '/../public/boardgames/' . $id . '/raw-data.xml';

        if (file_exists($cachedDataPath)) {
            return simplexml_load_string(
                file_get_contents($cachedDataPath)
            );
        }

        return null;
    }

    /**
     * @param int $id
     *
     * @return SimpleXMLElement
     */
    private function getBoardGameGeekData(int $id): SimpleXMLElement
    {
        $cachedDataPath = $this->rootDir . '/../public/boardgames/' . $id . '/raw-data.xml';

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->bggEndpoint . 'thing?type=boardgame&id=' . $id);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        curl_close($curl);

        $data = simplexml_load_string($result);

        $this->saveDataToFile(
            $cachedDataPath,
            $data->asXML()
        );

        return $data;
    }

    /**
     * @param string $dir
     * @param string $contents
     */
    private function saveDataToFile(string $dir, string $contents): void
    {
        $parts = explode('/', $dir);
        $file = array_pop($parts);
        $dir = '';
        foreach($parts as $part)
            if(!is_dir($dir .= "/$part")) mkdir($dir);
        file_put_contents("$dir/$file", $contents);
    }
}