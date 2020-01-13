<?php

namespace NK\SoapYaml;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

use NK\SoapYaml\Node;
use NK\SoapYaml\Exception\LoadException;

/**
 * Template Class
 * Load a Yaml Template and create SOAP request
 */
class Template
{
    /**
     * Root node
     * @var Node
     */
    protected $root = null;

    /**
     * Template data
     * @var array
     */
    protected $templateData = array();

    /**
     * Set data
     * 
     * @param array $data
     *
     * @return Template
     */
    public function setData($data)
    {
        $this->templateData = $data;

        return $this;
    }

    /**
     * Load Yaml file
     * 
     * @param  string $filename
     * 
     * @return boolean
     *
     * @throws LoadException
     */
    public function load($filename)
    {
        try {
            $data = Yaml::parseFile( $filename );
        }
        catch (ParseException $exception) {
            LoadException::throw($exception->getMessage());
        }

        if (!is_array($data)) {
            LoadException::throw('The yaml template is wrong');
        }

        if (count($data) == 0 || count($data) > 1) {
            LoadException::throw('A single main node was expected');
        }

        // Transform to nodes
        $name = key($data);
        $this->root = new Node($name, null, $data[$name]);

        return true;
    }

    /**
     * Update node values
     *
     * @param array  $data
     * @param Node   $fromNode
     */
    protected function updateValues($data, $fromNode)
    {
        foreach ($data as $key => $value) {
            // Syntax "node.attr" to modify attribute
            if (strpos($key, '.') !== false) {
                list($nodeName, $attrName) = explode('.', $key);
                $node = $fromNode->search( $nodeName );
                if (!is_null($node)) {
                    $node->modifyAttr($attrName, $value);
                }
            }
            else {
                $node = $fromNode->search( $key );
                if (!is_null($node)) {
                    if (is_array($value) && strcasecmp($node->getAttr('type'), 'array') !== 0) {
                        // Update recursive
                        $this->updateValues($value, $node);
                    }
                    else {
                        // Set value
                        $node->setValue( $value );
                    }
                }
            }
        }
    }

    /**
     * Export to XML (SOAP Request)
     * 
     * @param  integer $spaces
     * 
     * @return string
     */
    public function toXml($spaces = 0)
    {
        if (is_null($this->root)) {
            return null;
        }

        if (is_array($this->templateData) && count($this->templateData) > 0) {
            $this->updateValues($this->templateData, $this->root);
        }

        return $this->root->toXml( $spaces );
    }

    /**
     * Export to Yaml
     * 
     * @return string
     */
    public function toYaml()
    {
        $input = $this->root->toArray();
        return Yaml::dump($input, 10, 2);
    }

    /**
     * Load from XML
     * Use to create Yaml template from SOAP Request
     * 
     * @param  string $xml
     *
     * @return boolean
     */
    public function fromXml($xmlFilename)
    {
        libxml_use_internal_errors();

        $doc = new \DOMDocument('1.0', 'utf-8');
        if (false === $doc->load( $xmlFilename )) {
            return false;
        }

        $namespaces = array();
        $element = $doc->documentElement;
        $this->root = new Node($element->localName);
        $this->root->fromXml( $element, $namespaces );

        // Set namespaces
        foreach ($namespaces as $ns) {
            $name = "xmlns:$ns";
            $this->root->add($name, $element->getAttribute($name));
        }

        return true;
    }
}