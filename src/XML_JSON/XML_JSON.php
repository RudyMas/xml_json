<?php

namespace RudyMas\XML_JSON;

use RudyMas\FileManager\FileManager;
use SimpleXMLElement;

/**
 * Class XML_JSON (PHP version 7.0)
 * This class can be used to convert data between an array, XML and/or JSON
 *
 * This class is used in combination with following class:
 *    - FileManager (composer require rudymas/filemanager)
 *
 * @author      Rudy Mas <rudy.mas@rmsoft.be>
 * @copyright   2016 - 2017, rudymas.be. (http://www.rmsoft.be/)
 * @license     https://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
 * @version     0.6.1
 * @package     RudyMas\XML_JSON
 */
class XML_JSON
{
    private $arrayData;
    private $xmlData;
    private $jsonData;

    /**
     * Load XML file into $xmlData
     *
     * @param string $XMLfile Filename of the XML-file to read
     */
    public function loadXML(string $XMLfile)
    {
        $file = new FileManager();
        $this->xmlData = $file->loadLittleFile($XMLfile);
    }

    /**
     * Save $xmlData into XML file
     *
     * @param string $XMLfile Filename of the XML-file to write
     */
    public function saveXML(string $XMLfile)
    {
        $file = new FileManager();
        $file->saveLittleFile($this->xmlData, $XMLfile);
    }

    /**
     * Load JSON file into $jsonData
     *
     * @param string $JSONfile Filename of the JSON-file to read
     */
    public function loadJSON(string $JSONfile)
    {
        $file = new FileManager();
        $this->jsonData = $file->loadLittleFile($JSONfile);
    }

    /**
     * Save $jsonData into JSON file
     *
     * @param string $JSONfile Filename of the JSON-file to write
     */
    public function saveJSON(string $JSONfile)
    {
        $file = new FileManager();
        $file->saveLittleFile($this->jsonData, $JSONfile);
    }

    /**
     * Convert XML to Array
     */
    public function xml2array()
    {
        $this->xml2json();
        $this->arrayData = json_decode($this->jsonData, TRUE);
    }

    /**
     * Convert Array to XML
     *
     * @param string $xmlField The opening tag for the XML file
     * @param string|null $dataField
     */
    public function array2xml(string $xmlField = '', ?string $dataField = null)
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
    private function createXml(SimpleXMLElement $obj, array $array, ?string $prevKey = 'data')
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if (is_numeric($key)) {
                    $node = $obj->addChild($prevKey);
                    $this->createXml($node, $value);
                } elseif ($key == '@attributes') {
                    foreach ($value as $k => $v) {
                        $obj->addAttribute($k, $v);
                    }
                } else {
                    if ($prevKey != '' && !is_numeric($key)) $node = $obj->addChild($prevKey); else $node = $obj;
                    $this->createXml($node, $value, $key);
                }
            } else {
                if (is_numeric($key)) {
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
    public function json2array()
    {
        $this->arrayData = json_decode($this->jsonData, TRUE);
    }

    /**
     * Convert Array to JSON
     */
    public function array2json()
    {
        $this->jsonData = json_encode($this->arrayData);
    }

    /**
     * Convert XML to JSON
     */
    public function xml2json()
    {
        $xml = simplexml_load_string($this->xmlData, NULL, LIBXML_NOCDATA);
        $this->jsonData = json_encode($xml);
    }

    /**
     * Convert JSON to XML
     *
     * @param string $xmlField The opening tag for the XML file
     */
    public function json2xml(string $xmlField)
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
    public function setArrayData(array $arrayData)
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
    public function setXmlData(string $xmlData)
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
    public function setJsonData(string $jsonData)
    {
        $this->jsonData = $jsonData;
    }
}

/** End of File XML_JSON.php **/