<?php

namespace NK\SoapYaml;

use Symfony\Component\Yaml\Yaml;

/**
 * SOAP YAML Node class
 */
class Node
{
    /**
     * String constants
     * Used to avoid collisions
     */
    const S_ATTR        = '__attr__';
    const S_CHILD       = '__child__';

    /**
     * Name space
     * @var string
     */
    protected $namespace = null;

    /**
     * @var string
     */
    protected $tagName = '';

    /**
     * @var mixed
     */
    protected $tagValue;

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
     * 
     * @param array $data
     * @param Node  $parent
     */
    public function __construct(array $data = null, string $nodeName = 'root', Node $parent = null)
    {
        if (!is_null($data)) {
            $this->load( $data, $nodeName, $parent );
        }
    }

    /**
     * Get namespace
     * 
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Set namespace
     * 
     * @param string $ns
     *
     * @return Node
     */
    public function setNamespace($ns)
    {
        $this->namespace = $ns;

        return $this;
    }

    /**
     * Propagate namespace
     * 
     * @param  string  $ns
     * @param  boolean $force
     */
    public function propagateNamespace($ns, $force = false)
    {
        if (is_null($this->namespace) || $force) {
            $this->namespace = $ns;

            foreach ($this->childs as $name => $node) {
                $node->propagateNamespace( $ns );
            }
        }
    }

    /**
     * get attributes
     * 
     * @return array
     */
    public function getAttrs()
    {
        return $this->attrs;
    }

    /**
     * Get Attribute
     * 
     * @param  string $name
     * @param  mixed  $default
     * 
     * @return mixed
     */
    public function getAttr($name, $default = null)
    {
        if (array_key_exists($name, $this->attrs)) {
            return $this->attrs[ $name ];
        }

        return $default;
    }

    /**
     * Add Attribute
     * 
     * @param string $name
     * @param mixed  $value
     *
     * @return Node
     */
    public function addAttr($name, $value)
    {
        if (strpos($name, self::S_ATTR) === 0) {
            $name = substr($name, strlen(self::S_ATTR));
        }

        $this->attrs[ $name ] = $value;
    }

    /**
     * get childs
     * 
     * @return array
     */
    public function getChilds()
    {
        return $this->childs;
    }

    /**
     * Get child
     * 
     * @param  string $name
     * @param  mixed  $default
     * 
     * @return mixed
     */
    public function getChild($name, $default = null)
    {
        if (array_key_exists($name, $this->childs)) {
            return $this->childs[ $name ];
        }

        return $default;
    }

    /**
     * Add Child
     * 
     * @param string $name
     * @param array  $childData
     */
    public function addChild($name, $childData)
    {
        if (strpos($name, self::S_CHILD) === 0) {
            $name = substr($name, strlen(self::S_CHILD));
        }

        $this->childs[ $name ] = new Node($childData, $name, $this);
    }

    /**
     * Get attribute or child
     * 
     * @param  string $name
     * @param  mixed  $default
     * 
     * @return mixed
     */
    public function get($name, $default)
    {
        $value = $this->getAttr( $name );
        if (is_null($value)) {
            $value = $this->getChild( $name );
            if (is_null($value)) {
                $value = $default;
            }
        }

        return $value;
    }

    /**
     * From array generate tree of nodes
     * 
     * @param  array $data
     * @param  Node  $parent
     */
    public function load(array $data, string $nodeName = 'root', Node $parent = null)
    {
        $isNs = false;
        $this->tagName = $nodeName;
        $this->parent = $parent;

        foreach ($data as $name => $value) {
            if (($name == 'ns' || $name == 'namespace') && is_string($value)) {
                $this->setNamespace( $value );
                $isNs = true;
            }
            else if (is_array($value)) {
                $this->addChild($name, $value);
            }
            else {
                $this->addAttr($name, $value);
            }
        }

        if ($isNs) {
            $this->propagateNamespace( $this->namespace, true );
        }
    }

    /**
     * Convert to array
     *
     * @return array
     */
    public function toArray()
    {
        $result = array();

        // store namespace
        if (is_null($this->parent) || ($this->namespace != $this->parent->getNamespace())) {
            $result['ns'] = $this->namespace;
        }

        // Atributes
        foreach ($this->attrs as $attrName => $attrValue) {
            // avoid colisions
            if (array_key_exists($attrName, $this->childs)) {
                $attrName = self::S_ATTR . $attrName;
            }

            $result[ $attrName ] = $attrValue;
        }

        // Childs
        foreach ($this->childs as $cName => $cValue) {
            // avoid collisions
            if (array_key_exists($cName, $this->attrs)) {
                $cName = self::S_CHILD . $cName;
            }

            $result[ $cName ] = $cValue->toArray();
        }

        return $result;
    }

    /**
     * Returns a SOAP xml request
     */
    public function toXml()
    {
        if ($this->tagName)
        if (empty($this->namespace)) {
            $tag = $this->tagName;
        }
        else {
            $tag = $this->namespace .':'. $this->tagName;
        }
        
        if (empty($this->attrs) && empty($this->childs)) {
            return sprintf("<%s/>", $tag);
        }



        return $xml;
    }
}