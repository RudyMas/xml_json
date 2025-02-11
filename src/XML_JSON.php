<?php

namespace RudyMas;

use DOMDocument;
use SimpleXMLElement;

/**
 * Class XML_JSON (PHP version 7.4)
 * This class can be used to convert data between an array, XML and/or JSON
 *
 * @author      Rudy Mas <rudy.mas@rmsoft.be>
 * @copyright   2016 - 2025, rudymas.be. (http://www.rmsoft.be/)
 * @license     https://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
 * @version     0.9.0.0
 * @package     RudyMas
 */
class XML_JSON
{
    private $arrayData;
    private $xmlData;
    private $jsonData;
    private $csvData;

    /**
     * Load XML file into $xmlData
     *
     * @param string $XMLfile Filename of the XML-file to read
     */
    public function loadXML(string $XMLfile): void
    {
        $file = new FileManager();
        $this->xmlData = $file->loadLittleFile($XMLfile);
    }

    /**
     * Save $xmlData into XML file
     *
     * @param string $XMLfile Filename of the XML-file to write
     */
    public function saveXML(string $XMLfile): void
    {
        $file = new FileManager();
        $file->saveLittleFile($this->xmlData, $XMLfile);
    }

    /**
     * Load JSON file into $jsonData
     *
     * @param string $JSONfile Filename of the JSON-file to read
     */
    public function loadJSON(string $JSONfile): void
    {
        $file = new FileManager();
        $this->jsonData = $file->loadLittleFile($JSONfile);
    }

    /**
     * Save $jsonData into JSON file
     *
     * @param string $JSONfile Filename of the JSON-file to write
     */
    public function saveJSON(string $JSONfile): void
    {
        $file = new FileManager();
        $file->saveLittleFile($this->jsonData, $JSONfile);
    }

    /**
     * Load CSV file into $csvData
     *
     * @param string $CSVfile Filename of the JSON-file to read
     */
    public function loadCSV(string $CSVfile): void
    {
        $file = new FileManager();
        $this->csvData = $file->loadLittleFile($CSVfile);
    }

    /**
     * Save $csvData into CSV file
     *
     * @param string $CSVfile Filename of the CSV-file to write
     */
    public function saveCSV(string $CSVfile): void
    {
        $file = new FileManager();
        $file->saveLittleFile($this->csvData, $CSVfile);
    }

    /**
     * Convert XML to Array
     */
    public function xml2array(): void
    {
        $previous_value = libxml_use_internal_errors(true);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->loadXml($this->xmlData);
        libxml_use_internal_errors($previous_value);
        if (libxml_get_errors()) {
            $this->arrayData = ['xml_error' => true];
        } else {
            $array = $this->Dom2Array($dom);
            if (isset($array['Document'])) {
                $array = $array['Document'];
            }
            $this->arrayData = $array;
        }
    }

    /**
     * Make an Array from the XLM DOMDocument
     *
     * @param $root
     * @return array|mixed
     */
    private function Dom2Array($root)
    {
        $result = array();

        if ($root->hasAttributes()) {
            $attrs = $root->attributes;
            foreach ($attrs as $attr) {
                $result['@attributes'][$attr->name] = $attr->value;
            }
        }

        if ($root->hasChildNodes()) {
            $children = $root->childNodes;
            if ($children->length == 1) {
                $child = $children->item(0);
                if (in_array($child->nodeType, [XML_TEXT_NODE, XML_CDATA_SECTION_NODE])) {
                    $result['_value'] = $child->nodeValue;
                    return count($result) == 1
                        ? $result['_value']
                        : $result;
                }

            }
            $groups = array();
            foreach ($children as $child) {
                if (!isset($result[$child->nodeName])) {
                    $result[$child->nodeName] = $this->Dom2Array($child);
                } else {
                    if (!isset($groups[$child->nodeName])) {
                        $result[$child->nodeName] = array($result[$child->nodeName]);
                        $groups[$child->nodeName] = 1;
                    }
                    $result[$child->nodeName][] = $this->Dom2Array($child);
                }
            }
        }

        return $result;
    }

    /**
     * Convert Array to XML
     *
     * @param string $xmlField The opening tag for the XML file
     * @param string|null $dataField
     */
    public function array2xml(string $xmlField = '', ?string $dataField = null): void
    {
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><' . $xmlField . '/>');
        $this->createXml($xml, $this->arrayData, $dataField);
        $this->xmlData = $xml->asXML();
    }

    /**
     * Private method to create XML output
     *
     * @param SimpleXMLElement $obj
     * @param array $array
     * @param null|string $prevKey
     */
    private function createXml(SimpleXMLElement &$obj, array $array, ?string $prevKey = 'data'): void
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if (isset($value[0])) {
                    foreach ($value as $subValue) {
                        if (is_array($subValue)) {
                            $node = $obj->addChild($key);
                            $this->createXml($node, $subValue, $key);
                        } else {
                            $obj->addChild($key, $subValue);
                        }
                    }
                } elseif ($key == '@attributes') {
                    foreach ($value as $k => $v) {
                        $obj->addAttribute($k, $v);
                    }
                } else {
                    if (isset($value['_value'])) {
                        $node = $obj->addChild($key, $value['_value']);
                    } else {
                        $node = $obj->addChild($key);
                    }
                    $this->createXml($node, $value, $key);
                }
            } else {
                if ($key == '_value') {
                    // This value is already set!!!
                } elseif (is_numeric($key)) {
                    $obj->addChild($prevKey, $value);
                } else {
                    $obj->addChild($key, $value);
                }
            }
        }
    }

    /**
     * Convert JSON to Array
     */
    public function json2array(): void
    {
        $this->arrayData = json_decode($this->jsonData, TRUE);
    }

    /**
     * Convert Array to JSON
     */
    public function array2json(): void
    {
        $this->jsonData = json_encode($this->arrayData);
    }

    /**
     * Convert XML to JSON
     */
    public function xml2json(): void
    {
        $xml = @simplexml_load_string($this->xmlData, NULL, LIBXML_NOCDATA);
        if ($xml === false) {
            $this->jsonData = '{"xml_error" : "true"}';
        } else {
            $this->jsonData = json_encode($xml);
        }
    }

    /**
     * Convert CSV to array
     *
     * @param string $delimiter
     * @param bool $dataLine
     * @return void
     */
    public function csvToArray(string $delimiter = ';', bool $dataLine = true): void
    {
        $lines = explode("\n", $this->csvData);
        $header = null;
        $data = [];

        foreach ($lines as $line) {
            if (trim($line) === '') continue; // Skip empty lines
            $row = str_getcsv($line, $delimiter);
            if (!$header && $dataLine) {
                $header = $row;
            } elseif ($dataLine === false) {
                $data[] = $row;
            } else {
                $data[] = array_combine($header, $row);
            }
        }

        $this->setArrayData($data);
    }

    /**
     * Convert array to CSV
     *
     * @param string $delimiter
     * @param string $enclosure
     * @return void
     */
    public function arrayToCsv(string $delimiter = ';', string $enclosure = '"'): void
    {
        // Extract headers from the first element of the array
        $header = array_keys($this->arrayData[0]);
        $csv = '';

        // Open a memory "file" for read/write...
        $f = fopen('php://memory', 'r+');

        // Write the headers
        fputcsv($f, $header, $delimiter, $enclosure);

        // Write the data
        foreach ($this->arrayData as $row) {
            fputcsv($f, $row, $delimiter, $enclosure);
        }

        // Rewind the "file" and read its content
        rewind($f);
        $csv = stream_get_contents($f);

        // Close the memory "file"
        fclose($f);

        $this->setCsvData($csv);
    }

    /**
     * Convert JSON to XML
     *
     * @param string $xmlField The opening tag for the XML file
     */
    public function json2xml(string $xmlField): void
    {
        $this->json2array();
        $this->array2xml($xmlField);
    }

    /**
     * @return array
     */
    public function getArrayData(): array
    {
        return $this->arrayData;
    }

    /**
     * @param array $arrayData
     */
    public function setArrayData(array $arrayData): void
    {
        $this->arrayData = $arrayData;
    }

    /**
     * @return string
     */
    public function getXmlData(): string
    {
        return $this->xmlData;
    }

    /**
     * @param string $xmlData
     */
    public function setXmlData(string $xmlData): void
    {
        $this->xmlData = $xmlData;
    }

    /**
     * @return string
     */
    public function getJsonData(): string
    {
        return $this->jsonData;
    }

    /**
     * @param string $jsonData
     */
    public function setJsonData(string $jsonData): void
    {
        $this->jsonData = $jsonData;
    }

    /**
     * @return string
     */
    public function getCsvData(): string
    {
        return $this->csvData;
    }

    /**
     * @param string $csvData
     */
    public function setCsvData(string $csvData): void
    {
        $this->csvData = $csvData;
    }
}
