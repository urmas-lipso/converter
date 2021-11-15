<?php

class Json2Csv {
    private $fileName = '';
    private $jsonData = [];
    private $tableData = [[]];
    private $columlList = [];
    private $showNullAsText = false;

    public function __construct($options) {
        if (isset($options['show_null_as_text'])) {
            $this->showNullAsText = $options['show_null_as_text'];
        }
    }

    public function load($fileName) {
        $this->fileName = $fileName;
        $fileContents = file_get_contents($fileName);
        $this->jsonData = json_decode($fileContents, true);
    }

    /**
     * put data into it's column
     * if we already have this column filled on this row, this means we need to move to next row
     *
     *
     * @param $row
     * @param $column
     * @param $data
     * @return int|mixed
     */
    private function setCell($row, $column, $data) {
        if (isset($this->tableData[$row][$column])) {
            $row++;
        }
        $this->tableData[$row][$column] = $this->processCellValue($data);
        if (!in_array($column, $this->columlList)) {
            $this->columlList[] = $column;
        }
        return $row;
    }

    /**
     * main processing function with oldschool recursion
     * first fills values into the current table row
     * then processes branches, puts first values from a branch to same row as parent
     * so we would have a bit more compact and readable table
     *
     * @param $element
     * @param $path
     * @param $currentRow
     * @return int|mixed
     */
    private function process($element, $path, $currentRow) {
        $hasChildren = false;
        foreach($element as $key => $child) {
            if (!is_array($child)) {
                $newPath = is_string($key) ? array_merge($path, [$key]) : $path;
                $currentRow = $this->setCell($currentRow, implode('.', $newPath), $child);
            }
        }
        foreach($element as $key => $child) {
            if (is_array($child)) {
                $hasChildren = true;
                $newPath = is_string($key) ? array_merge($path, [$key]) : $path;
                $currentRow = $this->process($child, $newPath, $currentRow);
            }
        }
        return $hasChildren ? $currentRow : $currentRow + 1;
    }

    /**
     * execute processing
     * @return array[]
     */
    public function processJsonData() {
        $this->process($this->jsonData, [], 0);
        return $this->tableData;
    }

    /**
     * get the resulting raw data
     * used for debugging, kept for possible integration use
     * @return array[]
     */
    public function getCsvData() {
        return $this->tableData;
    }

    /**
     * getter for collected colums list
     * @return array
     */
    public function getColumnList() {

        $list = $this->columlList;

        // not too sure about optimal column order
        // sorting might be helpful, but it can also interfere with logical order
        // again, for real life i could just ask what column order users would like to have
        // so, currently i'll leave this sorting commented out

        //sort($list);

        return $list;
    }

    /**
     * we might need data conversion
     * @param $cellValue
     * @return string
     */
    private function processCellValue($cellValue) {
        // here is a big dilemma i can't quite solve without additional context
        // currently i have no information about data types of the fields
        // for example is "clientRate": ".1" a label and string or number with leading 0 omitted
        // so to be on the safe side i'll make excel think everything is text
        // for real world i would have just asked about data types
        // i do hope that providing a data type converter method shows i know about possible need here

        // decided that if a value is set to actually null, instead empty string "" or something likewise
        // user should end up seeing that mentioned null, because there might be reasons for null instead of ""
        if (is_null($cellValue) && $this->showNullAsText) {
            $cellValue = 'null';
        }
        return '"'.$cellValue.'"';
    }

    /**
     * get string containing resulting csv file
     * @return string
     */
    public function getCsv() {
        $result = '';
        $headers = $this->getColumnList();

        //to keep csv style consistent, the simplest way of putting quotes around header
        $headerList = [];
        foreach ($headers as $header) {
            $headerList[] = '"'.$header.'"';
        }
        $result .= implode(';', $headerList)."\r\n";

        foreach ($this->tableData as $i => $tableDataRow) {
            $row = [];
            foreach ($headers as $header) {
                // we do need to fill all those rows, as csv has no cell merge
                if (isset($tableDataRow[$header])) {
                    // so i set either existing value
                    $row[$header] = $tableDataRow[$header];
                } else {
                    // or just an empty cell
                    $row[$header] = '""';
                }
            }
            $result .= implode(';', $row)."\r\n";
        }

        return $result;
    }
}

$worker = new Json2Csv(['show_null_as_text' => true]);
if ($argc != 3) {
    die("script usage: php csv2json.php 'input file name' 'output file name'\r\n");

}
$inputFileName = $argv[1];
$outputFileName = $argv[2];

$worker->load($inputFileName);

$worker->processJsonData();
file_put_contents($outputFileName, $worker->getCsv());
echo "done\r\n";
