<?php

namespace NK\SoapYaml;

use Symfony\Component\Yaml\Yaml;

/**
 * SOAP YAML Node class
 */
class Node
{
    /**
     * Name space
     * @var string
     */
    protected $namespace = 'ns';

    /**
     * Attributes
     * @var array
     */
    protected $attrs = array();

    /**
     * Childs
     * @var array
     */
    protected $childs = array();

    /**
     * Parent node
     * @var null
     */
    protected $parent = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        # code...
    }
}