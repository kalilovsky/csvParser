<?php

namespace Classe;

class MetaDataParser{
    private $metaData = [];
    /**
     * Mutateur de la propriéte meta qui fait référence à la structure des données souhaitée
     *
     * @param array $lineFromMetaFile
     * @return void
     */
    public function setMetaData($lineFromMetaFile = []):void{
        $lineFromMetaFile[1] = (int)$lineFromMetaFile[1];
        
        $this->metaData[] = ['columnName'=>$lineFromMetaFile[0],
                             'columnLength'=>$lineFromMetaFile[1],
                             'columnType'=>$lineFromMetaFile[2]];
    }
    /**
     * L'accesseur qui retourne la structure des données
     *
     * @return array
     */
    public function getMetaData():array{
        return $this->metaData;
    }
    /**
     * Récupération du nombre de colonne souhaité d'aprés la structure de données
     *
     * @return integer
     */
    public function getColumnCount() : int{
        return count($this->metaData);
    }

    public function parseMetaDataFromFile(string $path) : void{
        if (($handle = fopen($path, "r", true)) !== FALSE) {
            while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
                $this->setMetaData($data);
            }
            fclose($handle);
        }
    }

}