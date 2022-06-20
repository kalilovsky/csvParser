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

    public function startParse() : void{
        
        $this->createCsvFile();
        $this->insertColumnName();
        $this->openRawDataFile();
        $this->rawToCsv();
        $this->closeContextFile();
        
    }

    private function checkData( string $data, array $actualColumnMetaData): string{
        switch($actualColumnMetaData['columnType']){
            case 'string':
                $data = rtrim($data);
                break;
            case 'numeric' :
                $data = floatval($data);
                break;
            case 'date' :
                if(!preg_match('([12]\d{3}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01]))',$data)){
                    throw new Exception('Date à la ligne ' . $this->currentLineInFile . ' n\'est pas conforme au format YYYY/MM/DD');
                }
                break;
            default:
                throw new Exception('Type de données non reconnu à la ligne ' . $this->currentLineInFile . PHP_EOL);
                break;
        }
        return $data;
    }

    private function insertColumnName():void{
        for($i=0;$i<$this->metaDataColumnCount;$i++){
            $data[]=strval($this->metaDataArray[$i]['columnName']);
        }
        // écriture UTF8 en mode binaire dans le fichier
        fputs($this->csvFileDataContext, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
        // la fonction PHP prend par défaut le CRLF grace à la constante système PHP_EOL
        fputcsv($this->csvFileDataContext,$data);
    }

    private function createCsvFile():void{
        if(!$this->csvFileDataContext = fopen($this->destinationPath,'w')){
            throw new Exception('Erreur survenue lors de la création du fichier CSV.' . PHP_EOL);
        }
        echo('Fichier CSV crée avec succées' . PHP_EOL);
    }

    private function openRawDataFile():void{
        if(!$this->fileRawDataContext = fopen($this->sourcePath, "r")){
            throw new Exception('Erreur survenue lors du chargement des données brutes.' . PHP_EOL);
        }
        echo('Fichier de données brute chargé avec succées.' . PHP_EOL);
    }

    private function rawToCsv():void{
        while (!feof($this->fileRawDataContext)) {
            $this->dataByLine = fgets($this->fileRawDataContext);

            if(strlen($this->dataByLine) < $this->metaData->getStrLenByLine()) 
            throw new Exception('Ligne vide détéctée à la ligne ' . $this->currentLineInFile . PHP_EOL);
            $data =[];
            for($i =0 ; $i < $this->metaDataColumnCount ; $i++){
                $dataTemp = substr($this->dataByLine,0,$this->metaDataArray[$i]['columnLength']);
                $data[] = $this->checkData($dataTemp,$this->metaDataArray[$i]);
                $this->dataByLine = substr($this->dataByLine,$this->metaDataArray[$i]['columnLength']);
            }
            fputcsv($this->csvFileDataContext,$data,',');
            $this->currentLineInFile ++;
        }
    }

    public function closeContextFile():void{
        fclose($this->fileRawDataContext);
        fclose($this->csvFileDataContext);
    }
}   