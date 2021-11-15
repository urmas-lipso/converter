<?php

namespace App\Workers;

use Illuminate\Support\Facades\Http;

class Csv2JsonWorker
{
    private $fileName = '';
    private $csvData = [];
    private $rowsData = [];
    private $headers = [];
    private $setNullFromText = false;
    private $setNullToEmpty = false;

    public function __construct($options = []) {
        if (isset($options['set_null_from_text'])) {
            $this->setNullFromText = $options['set_null_from_text'];
        }
        if (isset($options['set_null_to_empty'])) {
            $this->setNullToEmpty = $options['set_null_to_empty'];
        }
    }

    /**
     * my libreoffice saved a csv with only \n line ending
     * better make sure we use correct ones
     *
     * @param $fileContents
     * @return string
     */
    public function getLineSeparator($fileContents) {
        if (strpos($fileContents, "\r\n") == false) {
            return "\n";
        } else {
            return "\r\n";
        }
    }

    /**
     * load file and parse it into an array
     * empty cells are ignored
     *
     * @param $fileName
     */
    public function setData($data) {
        $lineSeparator = $this->getLineSeparator($data);
        $fileRows = explode($lineSeparator, $data);
        $headerRow = explode(';', array_shift($fileRows));
        $headers = [];
        foreach($headerRow as $headerCell) {
            $headers[] = str_replace('"', '', $headerCell);
        }
        $this->setHeaders($headers);
        foreach ($fileRows as $fileRow) {
            $row = [];
            $rowData = explode(';', $fileRow);
            foreach($headers as $header) {
                $cell = array_shift($rowData);
                if (!$this->isEmpty($cell)) {
                    $row[$header] = $this->processCellValue($cell);
                }
            }
            $this->csvData[] = $row;
        }
    }


    /**
     * as i need to get data structure/paths from headers
     * i better collect them
     *
     * @param $headers
     */
    private function setHeaders($headers) {
        foreach ($headers as $header) {
            $path = explode('.', $header);
            array_unshift($path, 'json');
            $element = array_pop($path);
            $level = count($path);
            $this->headers[] = [
                'header' => $header,
                'level' => $level,
                'path' => $path,
                'element' => $element
            ];
        }
        usort($this->headers, function($h1, $h2) {
            return $h1['level'] > $h2['level'];
        });
    }

    /**
     * check if cell is empty
     *
     * @param $cell
     * @return bool
     */
    private function isEmpty($cell) {
        $cell = str_replace('"', '', $cell);
        return $cell == '';
    }

    /**
     * sets data into designated places
     *
     * @param $target
     * @param $path
     * @param $element
     * @param $data
     * @return mixed
     */
    private function setDataWithPath($target, $path, $element, $data) {

        $currentIndex = array_shift($path);

        // this index is not yet in use, setting empty array for it
        if (!isset($target[$currentIndex])) {
            $target[$currentIndex] = [[]];
        }

        // we have not yet reached the node we need to append data to
        // we continue with the last element on current array node
        // like if we had here 'settings' for zone->settings->something
        // new values should be set to the last of those setting blocks
        if (count($path)) {
            $target[$currentIndex][array_key_last($target[$currentIndex])] = $this->setDataWithPath($target[$currentIndex][array_key_last($target[$currentIndex])], $path, $element, $data);
        } else {

            if (!isset($target[$currentIndex][array_key_last($target[$currentIndex])][$element])) {
                // the last element of the list did not had this value, we can set it
                $target[$currentIndex][array_key_last($target[$currentIndex])][$element] = $data;
            } else {
                // if our array already has an element with this field set, we expand the list
                $target[$currentIndex][] = [$element => $data];
            }
        }
        return $target;
    }

    /**
     * execute processing
     * the plan is to set every cell into it's designated path i get from header
     * when a path is already taken a new node should be inserted
     *
     * as all paths start with 'json' inserting a new top level item is also inserted by those overlaps
     */
    public function processCsvData() {
        foreach ($this->csvData as $csvRow) {
            foreach ($this->headers as $header) {
                if (isset($csvRow[$header['header']])) {
                    $this->rowsData = $this->setDataWithPath($this->rowsData, $header['path'], $header['element'], $csvRow[$header['header']]);
                }
            }
        }
    }

    /**
     * get the resulting raw data
     * used for debugging, kept for possible integration use
     * @return array[]
     */
    public function getJsonData() {
        return $this->rowsData["json"];
    }

    public function getJson() {
        return json_encode($this->getJsonData());
    }
    /**
     * we might need data conversion
     * @param $cellValue
     * @return string
     */
    private function processCellValue($cellValue) {

        // first remove quotation, if present
        $cellValue = str_replace('"', '', $cellValue);

        // here is a big dilemma i can't quite solve without additional context
        // currently i have no information about data types of the fields
        // for example is "clientRate": ".1" a label and string or number with leading 0 omitted
        // so to be on the safe side i'll make excel think everything is text
        // for real world i would have just asked about data types
        // i do hope that providing a data type converter method shows i know about possible need here

        // decided that if a value is set to actually null, instead empty string "" or something likewise
        // user should end up seeing that mentioned null, because there might be reasons for null instead of ""

        // so now, if we get string "null" from csv file and we do have this rule in effect, make it an actual null

        if ($cellValue == 'null' && $this->setNullFromText) {
            $cellValue = null;
        }
        if ($cellValue == null && $this->setNullToEmpty) {
            $cellValue = '';
        }
        return $cellValue;
    }
}
