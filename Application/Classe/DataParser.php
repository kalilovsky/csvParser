<?php
namespace Classe;

use Exception;

class DataParser{
    private $fileRawDataContext;
    private $csvFileDataContext;
    private $dataByLine;
    private $metaDataColumnCount;
    private $metaDataArray;
    private $metaData;
    private $sourcePath;
    private $destinationPath;
    private $currentLineInFile =1;

    public function __construct(string $sourcePath, string $destinationPath=null,MetaDataParser $metaData)
    {
        $this->metaDataColumnCount = $metaData->getColumnCount();
        $this->metaDataArray = $metaData->getMetaData();
        $this->sourcePath = $sourcePath;
        $this->destinationPath = $destinationPath;
        $this->metaData = $metaData;
    }
    /**
     * Fonction moteur de la classe
     *
     * @return void
     */
    public function startParse() : void{
        
        $this->createCsvFile();
        $this->insertColumnName();
        $this->openRawDataFile();
        $this->rawToCsv();
        $this->closeContextFile();
        
    }
    /**
     * Fonction qui vérifie les données enleve les espace à droite d'une chaine de caractère, convertie une chaine en nombre flottan, vérifie le format de la date, et retourne la donnée.
     *
     * @param string $data données qui vient du fichier de données brute
     * @param array $actualColumnMetaData le type de données attendus.
     * @return string
     * @throws Exception Retourne une erreur si la date n'est pas au bon format, ou aucunes des données n'a été trouvées.
     * @throws Exception Lance une erreur si la valeur qui est sensé être numérique contient autre chose que  , + - ou les nombres
     */
    private function checkData( string $data, array $actualColumnMetaData): string{
        switch($actualColumnMetaData['columnType']){
            case 'string':
                $data = rtrim($data);
                break;
            case 'numeric' :
                if(!preg_match('/^\s*[+-]?(\d+\.\d+|\d+\.\d*|\d+)$/',$data)){
                        $this->throwEx('La valeur numérique à la ligne ' . $this->currentLineInFile . ' n\'est pas conforme, elle ne contient pas uniquement des chiffres');
                }
                $data = floatval($data);
                break;
            case 'date' :
                if(!preg_match('([12]\d{3}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01]))',$data)){
                    $this->throwEx('Date à la ligne ' . $this->currentLineInFile . ' n\'est pas conforme au format YYYY/MM/DD');
                }
                break;
            default:
                $this->throwEx('Type de données non reconnu à la ligne ' . $this->currentLineInFile );
                break;
        }
        return $data;
    }
    /**
     * Insetion de la premiere ligne qui représente le nom des colonnes dans le fichier CSV.
     *
     * @return void
     */
    private function insertColumnName():void{
        foreach($this->metaDataArray as $metaData){
            $data[]=strval($metaData['columnName']);
        }
        // écriture UTF8 en mode binaire dans le fichier
        fputs($this->csvFileDataContext, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
        // la fonction PHP prend par défaut le CRLF grace à la constante système PHP_EOL
        fputcsv($this->csvFileDataContext,$data);
    }
    /**
     * Création du fichier CSV
     *
     * @return void
     * @throws Exception Création du fichier impossible
     */
    private function createCsvFile():void{
        if(!$this->csvFileDataContext = fopen($this->destinationPath,'w')){
            $this->throwEx('Erreur survenue lors de la création du fichier CSV.');
        }
        echo('Fichier CSV crée avec succées' . PHP_EOL);
    }
    /**
     * Ouverture du fichier de données brut
     *
     * @return void
     * @throws Exception Lancement d'erreur si erreur lors de l'ouverture
     */
    private function openRawDataFile():void{
        if(!$this->fileRawDataContext = fopen($this->sourcePath, "r")){
            $this->throwEx('Erreur survenue lors du chargement des données brutes.');
        }
        echo('Fichier de données brute chargé avec succées.' . PHP_EOL);
    }
    /**
     * Convertion des données brut en données CSV
     *
     * @return void
     * @throws Exception Format de la ligne n'est pas conforme
     */
    private function rawToCsv():void{
        while (!feof($this->fileRawDataContext)) {
            $this->dataByLine = fgets($this->fileRawDataContext);
            $t = strlen($this->dataByLine);
            $z = $this->metaData->getStrLenByLine();
            if(!(strlen($this->dataByLine) == $this->metaData->getStrLenByLine())) 
            $this->throwEx('Données non conformes détéctée à la ligne ' . $this->currentLineInFile);
            $data =[];
            foreach($this->metaDataArray as $metaData){
                $dataTemp = substr($this->dataByLine,0,$metaData['columnLength']);
                $data[] = $this->checkData($dataTemp,$metaData);
                $this->dataByLine = substr($this->dataByLine,$metaData['columnLength']);
            }
            if(strlen($this->dataByLine)>1){
               $this->throwEx('Ligne n°' . $this->currentLineInFile . ' non conforme à ce qui est attendue.');
            }
            fputcsv($this->csvFileDataContext,$data,',');
            $this->currentLineInFile ++;
        }
    }
    /**
     * Fermeture des fichiers
     *
     * @return void
     */
    private function closeContextFile():void{
        fclose($this->fileRawDataContext);
        fclose($this->csvFileDataContext);
    }
    /**
     * Lancement des Exceptions
     *
     * @param string $message
     * @return void
     */
    private function throwEx(string $message):void{
        throw new Exception($message.PHP_EOL);
    }
}   