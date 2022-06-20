<?php

use Classe\DataParser;
use Classe\MetaDataParser;


class Application
{


    /**
     * Fonction principale qui permet de lancer l'application
     *
     * @return void
     */
    public static function start(): void
    {   
        echo ("Bonjour vous êtes sur l'outil de conversion d'un fichier avec données brut en fichier CSV". PHP_EOL);
        echo "Veuillez entrer le chemin complet du fichier métadonnées..." . PHP_EOL;
        $metaDataFilePath = readline();
        echo "Veuillez entrer le chemin complet du fichier que vous voulez convertir..." . PHP_EOL;
        $rawDataFilePath = readline();
        echo "Veuillez entrer le chemin ou vous voulez enregistrez le fichier CSV de sortie..." . PHP_EOL;
        $csvFilePath = readline();
        // /home/khalil/portfolio/Projet_13/Octo/fffc/Test/data2/meta.csv
        // /home/khalil/portfolio/Projet_13/Octo/fffc/Test/data2/data.txt
        // /home/khalil/portfolio/Projet_13/Octo/fffc/Test/data2/data.csv

        // https://khalil.alwaysdata.net/ressources/meta.csv
        // https://khalil.alwaysdata.net/ressources/data.txt
        try{
            $metaData = new MetaDataParser();
            $metaData->parseMetaDataFromFile($metaDataFilePath);
            $dataParser = new DataParser($rawDataFilePath,$csvFilePath,$metaData);
            $dataParser->startParse();
            echo('Traitement fini'.PHP_EOL);
            echo('Votre fichier est disponible au chemin suivant : ' . PHP_EOL . $csvFilePath . PHP_EOL);
        } catch (Exception $e){
            echo 'Exception reçue : ' . $e->getMessage() . PHP_EOL;
        }

    }

}
