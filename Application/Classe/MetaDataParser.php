<?php

namespace Classe;

use Exception;

class MetaDataParser
{
    /**
     * Tableau de chaine de caractére qui permettra de stockers les données attendus.
     *
     * @var array
     */
    private $metaData = [];
    /**
     * Variable qui stockera la longueur d'une ligne de données
     *
     * @var integer
     */
    private $strLenByLine = 0;
    /**
     * Mutateur de la propriéte meta qui fait référence à la structure des données souhaitée
     *
     * @param array $lineFromMetaFile
     * @return void
     */
    public function setMetaData($lineFromMetaFile = []): void
    {
        $lineFromMetaFile[1] = (int)$lineFromMetaFile[1];

        $this->metaData[] = [
            'columnName' => $lineFromMetaFile[0],
            'columnLength' => $lineFromMetaFile[1],
            'columnType' => $lineFromMetaFile[2]
        ];
    }
    /**
     * L'accesseur qui retourne la structure des données
     *
     * @return array
     */
    public function getMetaData(): array
    {
        return $this->metaData;
    }
    /**
     * Récupération du nombre de colonne souhaité d'aprés la structure de données
     *
     * @return integer
     */
    public function getColumnCount(): int
    {
        return count($this->metaData);
    }
    /**
     * Fonction qui récupere les structures des données à partir d'un fichier métadonnées CSV
     *
     * @param string $path
     * Chemin du fichier métadonnées
     * @return void
     * @throws Exception
     * Soit le fichier n'éxiste pas soit il y'a eu un autre probléme lors de l'ouverture du fichier
     */
    public function parseMetaDataFromFile(string $path): void
    {
        if (($handle = fopen($path, "r", true)) !== FALSE) {
            while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
                $this->setMetaData($data);
                $this->strLenByLine += (int) $data[1];
            }
            fclose($handle);
            echo 'Données de métadonnées chargées avec succées.' . PHP_EOL;
        } else {
            throw new Exception('Une érreur est survenue lors de l\'ouverture du fichier de métadonnées.' . PHP_EOL);
        }
    }

    /**
     * Retourne le nombre total des caractére d'une ligne de données
     *
     * @return integer
     */
    public function getStrLenByLine(): int
    {
        return ($this->strLenByLine + 1);
    }
}
