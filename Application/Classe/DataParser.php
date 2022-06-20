<?php
namespace Classe;

class DataParser{
    // private static $fileDataContext;
    private static $csvFileDataContext;
    private static $dataByLine;
    public static function startParse(string $sourcePath, string $destinationPath=null,MetaDataParser $metaData){
        
        $metaDataColumnCount = $metaData->getColumnCount();
        $metaDataArray = $metaData->getMetaData();
        self::$csvFileDataContext = fopen($destinationPath,'w');
        self::insertColumnName($destinationPath,$metaDataArray,$metaDataColumnCount);
        $fileDataContext = fopen($sourcePath, "r");
        while (!feof($fileDataContext)) {
            self::$dataByLine = fgets($fileDataContext);
            $data =[];
            for($i =0 ; $i < $metaDataColumnCount ; $i++){
                $dataTemp = substr(self::$dataByLine,0,$metaDataArray[$i]['columnLength']);
                $data[] = self::checkData($dataTemp,$metaDataArray[$i]);
                self::$dataByLine = substr(self::$dataByLine,$metaDataArray[$i]['columnLength']);
            }

            fputcsv(self::$csvFileDataContext,$data,',',',');
        }
    }

    private static function checkData( string $data, array $actualColumnMetaData): string{
        switch($actualColumnMetaData['columnType']){
            case 'string':
                $data = rtrim($data);
                break;
            case 'numeric' :
                $data = floatval($data);
                break;
            case 'date' :
                // $data = floatval($data);
                break;
        }
        return $data;
    }

    private static function insertColumnName($path,$array,$count){
        for($i=0;$i<$count;$i++){
            $data[]=$array[$i]['columnName'];
        }
        fputcsv(self::$csvFileDataContext,$data,',', chr(0));
    }

    // private static function createCsvFile(string $path){
    //     fputcsv($path,$data,',',',');
    // }

    // private static function openDataFile(string $path){
    //     self::$fileDataContext = $input = fopen($sourcePath, "r");
    // }
}   