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
    public function start(): void
    {   
        echo ("Bonjour vous êtes sur l'outil de conversion d'un fichier avec données brut en fichier CSV \n");
        try{
            $metaData = new MetaDataParser();
            $metaData->parseMetaDataFromFile("/home/khalil/portfolio/Projet_13/testtechnique/meta.csv");
            $dataParser = new DataParser("/home/khalil/portfolio/Projet_13/testtechnique/data.txt","/home/khalil/portfolio/Projet_13/testtechnique/data.csv",$metaData);
            $dataParser->startParse();
            echo('Traitement fini'.PHP_EOL);
        } catch (Exception $e){
            echo 'Exception reçue : ' . $e->getMessage() . PHP_EOL;
        }

    }

}
