<?php
namespace Classe;

use Exception;

class DataParser{
    protected $fileRawDataContext;
    protected $csvFileDataContext;
    protected $dataByLine;
    protected $metaDataColumnCount;
    protected $metaDataArray;
    protected $metaData;
    protected $sourcePath;
    protected $destinationPath;
    protected $currentLineInFile =1;
    const CRLF = "\r\n";

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
    protected function checkData( string $data, array $actualColumnMetaData): string{
        switch($actualColumnMetaData['columnType']){
            case 'string':
                $data = rtrim($data);
                break;
            case 'numeric' :
                if(!preg_match('/^\s*[+-]?(\d+\.\d+|\d+\.\d*|\d+)$/',$data)){
                        $this->throwEx('La valeur numérique à la ligne ' . ($this->currentLineInFile-1) . ' n\'est pas conforme, elle ne contient pas uniquement des chiffres');
                }
                $data = floatval($data);
                break;
            case 'date' :
                if(!preg_match('([12]\d{3}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01]))',$data)){
                    $this->throwEx('Date à la ligne ' . ($this->currentLineInFile -1) . ' n\'est pas conforme au format YYYY/MM/DD');
                }
                break;
            default:
                $this->throwEx('Type de données non reconnu à la ligne ' . ($this->currentLineInFile-1) );
                break;
        }
        return $data;
    }
    /**
     * Insetion de la premiere ligne qui représente le nom des colonnes dans le fichier CSV.
     *
     * @return void
     */
    protected function insertColumnName():void{
        foreach($this->metaDataArray as $metaData){
            $data[]=strval($metaData['columnName']);
        }
        // écriture UTF8 en mode binaire dans le fichier
        fputs($this->csvFileDataContext, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
        // la fonction PHP prend par défaut le CRLF grace à la constante système PHP_EOL
        if(!fputcsv($this->csvFileDataContext,$data,',')) $this->throwEx('Problème lors de l\'écriture de la ligne ' . ($this->currentLineInFile-1));
        // Vérification la présence du CRLF, si il n'est pas présent dans les deux derniers octets de la dérnieres ligne écrite, alors l'insérer
        if(!$this->stringChecker(-2, DataParser::CRLF)) $this->stringAdder(-1,DataParser::CRLF);
        $this->currentLineInFile ++;
    }
    /**
     * Création du fichier CSV
     *
     * @return void
     * @throws Exception Création du fichier impossible
     */
    protected function createCsvFile():void{
        if(!$this->csvFileDataContext = fopen($this->destinationPath,'w+')){
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
    protected function openRawDataFile():void{
        if (!Utils::fileExist($this->sourcePath)) $this->throwEx('Le fichier de données brutes n\'existe pas.' . PHP_EOL);
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
    protected function rawToCsv():void{
        while (!feof($this->fileRawDataContext)) {
            // On remplace tous les CR LF et CRLF de la ligne pour pouvoir faire une bonne vérification
            $this->dataByLine = str_replace(array("\r\n", "\n", "\r"),"", fgets($this->fileRawDataContext));
            // si la longuer de la ligne de la ligne brute est egale à la longuer de la ligne dans les metadonnées
            if(!(strlen($this->dataByLine) == $this->metaData->getStrLenByLine())) 
            $this->throwEx('Données non conformes détéctée à la ligne ' . $this->currentLineInFile);
            $data =[];
            // On vérifie le type de données
            foreach($this->metaDataArray as $metaData){
                $dataTemp = substr($this->dataByLine,0,$metaData['columnLength']);
                $data[] = $this->checkData($dataTemp,$metaData);
                $this->dataByLine = substr($this->dataByLine,$metaData['columnLength']);
            }
            if(strlen($this->dataByLine)>1){
               $this->throwEx('Ligne n°' . $this->currentLineInFile . ' non conforme à ce qui est attendue.');
            }
            // Formate la ligne et l'écrit dans le fichier
            if(!fputcsv($this->csvFileDataContext,$data,',')) $this->throwEx('Problème lors de l\'écriture de la ligne ' . $this->currentLineInFile);
            // Vérification la présence du CRLF, si il n'est pas présent dans les deux derniers octets de la dérnieres ligne écrite, alors l'insérer
            if(!$this->stringChecker(-2, DataParser::CRLF)) $this->stringAdder(-1,DataParser::CRLF);
            $m = $this->stringChecker(-2, DataParser::CRLF);
            $this->currentLineInFile ++;
        }
    }
    /**
     * Fermeture des fichiers
     *
     * @return void
     */
    protected function closeContextFile():void{
        fclose($this->fileRawDataContext);
        fclose($this->csvFileDataContext);
    }
    /**
     * Lancement des Exceptions
     *
     * @param string $message
     * @return void
     */
    protected function throwEx(string $message):void{
        throw new Exception($message.PHP_EOL);
    }

    /**
     * Vérification de la présence d'une présence d'une chaine de caractére
     *
     * @param integer $offest
     * Position à laquelle on souhaite se mettre dans le fichier.
     * @param string $string
     * La chaine que l'on veut rechercher
     * @return boolean
     */
    protected function stringChecker(int $offest, string $string):bool{
       if(fseek($this->csvFileDataContext,$offest,SEEK_END)==-1) $this->throwEx('Problème lors de la lécture de la ligne ' . ($this->currentLineInFile-1));
       return fgets($this->csvFileDataContext) === $string;
    }
    /**
     * Ajout d'une chaine de caractere dans une position précise dans un fichier
     *
     * @param integer $offest
     * Position à laquelle on souhaite se mettre dans le fichier.
     * @param string $string
     * La chaine que l'on veut ajouter
     * @return void
     */
    protected function stringAdder(int $offest, string $string){
        if(fseek($this->csvFileDataContext,$offest,SEEK_END)== -1) $this->throwEx('Problème lors de la lécture de la ligne ' . $this->currentLineInFile);
        if(!fwrite($this->csvFileDataContext,$string)) $this->throwEx('Problème lors de l\'écriture de la ligne ' . $this->currentLineInFile);
    }

}   