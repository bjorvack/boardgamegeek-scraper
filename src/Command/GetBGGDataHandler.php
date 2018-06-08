<?php

namespace App\Command;

use App\ValueObject\BoardGame;
use Exception;
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
        $boardGames = $this->getData($data->getIds());

        foreach ($boardGames as $boardGame) {
            $boardGame = $this->copyImageToLocal($boardGame);
            $this->saveXML($boardGame);
        }
    }

    /**
     * @param BoardGame $boardGame
     */
    private function saveXML(BoardGame $boardGame): void
    {
        $cachedXMLPath = $this->rootDir . '/../public/boardgames/' . $boardGame->getId() . '/data.xml';

        if (!file_exists($cachedXMLPath)) {
            $this->saveDataToFile(
                $cachedXMLPath,
                $boardGame->toXML()->asXML()
            );
        }
    }

    /**
     * @param BoardGame $boardGame
     *
     * @return BoardGame
     */
    private function copyImageToLocal(BoardGame $boardGame): BoardGame
    {
        $cachedImagePath = $this->rootDir . '/../public/boardgames/' . $boardGame->getId() . '/image.jpg';

        if (!file_exists($cachedImagePath)) {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $boardGame->getImage());
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $result = curl_exec($curl);
            curl_close($curl);

            $this->saveDataToFile(
                $cachedImagePath,
                $result
            );
        }

        return new BoardGame(
            $boardGame->getId(),
            $boardGame->getName(),
            $boardGame->getDescription(),
            '/boardgames/' . $boardGame->getId() . '/image.jpg'
        );
    }

    /**
     * @param array $ids
     *
     * @return BoardGame
     */
    private function getData(array $ids): array
    {
        $boardgames = [];

        try {
            $data = $this->getCachedData($ids);
            if (empty($data)) {
                $data = $this->getBoardGameGeekData($ids);
            }

            foreach ($data->children() as $child) {
                $boardgames[] = new BoardGame(
                    (int) $child->attributes()['id'],
                    $child->children()->name->attributes()['value'],
                    $child->children()->description,
                    $child->children()->image
                );
            }
        } catch (Exception $exception) {
            if (count($ids) !== 1) {
                $chunks = array_chunk($ids, ceil(count($ids) / 2));
                $boardgames = array_merge(
                    $this->getData($chunks[0]),
                    $this->getData($chunks[1])
                );
            }
        }

        return $boardgames;
    }

    /**
     * @param array $ids
     *
     * @return null|SimpleXMLElement
     */
    private function getCachedData(array $ids): ?SimpleXMLElement
    {
        $id = md5(implode(',', $ids));

        $cachedDataPath = $this->rootDir . '/../var/cache/boardgames/' . $id . '/raw-data.xml';

        if (file_exists($cachedDataPath)) {
            return simplexml_load_string(
                file_get_contents($cachedDataPath)
            );
        }

        return null;
    }

    /**
     * @param array $ids
     *
     * @return SimpleXMLElement
     */
    private function getBoardGameGeekData(array $ids): SimpleXMLElement
    {
        $id = implode(',', $ids);
        $cachedDataPath = $this->rootDir . '/../var/cache/boardgames/' . md5($id) . '/raw-data.xml';

        $url = $this->bggEndpoint . 'thing?type=boardgame&id=' . $id;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        curl_close($curl);

        $data = simplexml_load_string($result);

        if ($data->children()->count()) {
            $this->saveDataToFile(
                $cachedDataPath,
                $data->asXML()
            );
        }

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
        foreach ($parts as $part) {
            if (!is_dir($dir .= "/$part")) {
                mkdir($dir);
            }
        }
        file_put_contents("$dir/$file", $contents);
    }
}
