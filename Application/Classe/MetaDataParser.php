<?php

namespace Classe;

use Exception;

class MetaDataParser{
    private $metaData = [];
    private $strLenByLine = 0;
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
        if(file_exists($path)){
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
        }else{
            throw new Exception('Le fichier situé "'.$path.' n\'existe pas, veuillez relancer l\'application en introduisant le bon chemin pour le ficher de métadonnées.');
        }
    }

    public function getStrLenByLine():int{
        return $this->strLenByLine;
    }


}