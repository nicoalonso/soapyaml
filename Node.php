<?php

namespace NK\SoapYaml;

use NK\SoapYaml\Exception\LoadException;

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
    protected $tagValue = null;

    /**
     * Parent node
     * @var Node
     */
    protected $parent = null;

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
     * Constructor
     *
     * @param string $nodeName
     * @param array  $data
     */
    public function __construct(string $nodeName = 'root', Node $parent = null, array $data = null)
    {
        $this->setName( $nodeName );
        $this->setParent( $parent );

        if (!is_null($data)) {
            $this->load( $data );
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
     * Get Tag Name
     * 
     * @return string
     */
    public function getName()
    {
        return $this->tagName;
    }

    /**
     * Set Tag Name
     * 
     * @param string $name
     *
     * @return Node
     */
    public function setName($name)
    {
        $this->tagName = $name;

        return $this;
    }

    /**
     * Get Value
     * 
     * @return mixed
     */
    public function getValue()
    {
        return $this->tagValue;
    }

    /**
     * Set Value
     * 
     * @param mixed $value
     *
     * @return Node
     */
    public function setValue($value)
    {
        $this->tagValue = $value;

        return $this;
    }

    /**
     * Get Parent
     * 
     * @return Node
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set Parent
     * 
     * @param  Node $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;

        return $this;
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
     * Get attribute or child
     * 
     * @param  string $name
     * @param  mixed  $default
     * 
     * @return mixed
     */
    public function get($name, $default = null)
    {
        // return childs first
        $value = $this->getChild( $name );
        if (is_null($value)) {
            $value = $this->getAttr( $name );
            if (is_null($value)) {
                $value = $default;
            }
        }

        return $value;
    }

    /**
     * Call method magic for get objects
     * 
     * @param  string $name
     * @param  array $arguments
     * 
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (strncasecmp($name, 'get', 3) === 0) {
            return $this->get(strtolower(substr($name, 3)));
        }

        LoadException::throw("Method '$name' not exists");
    }

    /**
     * Add child or attribute
     * 
     * @param string|Node   $name  child name, attribute name or Node object
     * @param mixed         $data  array for add a new Node, else add attribute
     *
     * @throws LoadException
     * 
     * @return Node
     */
    public function add($name, $data = null)
    {
        if (is_object($name)) {
            // name is a Node object
            $node = $name;
            if (get_class($node) == 'NK\SoapYaml\Node') {
                $node->setParent( $this );
                $this->childs[ $node->getName() ] = $node;
            }
            else {
                LoadException::throw("Name must be a Node object");
            }
        }
        else if (is_string($name)) {
            if (is_array($data)) {
                // Add child
                if (strpos($name, self::S_CHILD) === 0) {
                    $name = substr($name, strlen(self::S_CHILD));
                }

                $this->childs[ $name ] = new Node($name, $this, $data);
            }
            else {
                // Add attribute
                if (strpos($name, self::S_ATTR) === 0) {
                    $name = substr($name, strlen(self::S_ATTR));
                }

                $this->attrs[ $name ] = $data;
            }
        }
        else {
            LoadException::throw("Name wrong value");
        }

        return $this;
    }

    /**
     * From array generate tree of nodes
     *
     * @param  array  $data
     */
    public function load(array $data)
    {
        $isNs = false;

        foreach ($data as $name => $value) {
            if ((strcasecmp($name, 'ns') === 0 || strcasecmp($name, 'namespace') === 0) && is_string($value)) {
                $this->setNamespace( $value );
                $isNs = true;
            }
            else if (strcasecmp($name, 'value') === 0) {
                $this->setValue( $value );
            }
            else {
                $this->add($name, $value);
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
        if (!is_null($this->namespace) && (is_null($this->parent) || ($this->namespace != $this->parent->getNamespace()))) {
            $result['ns'] = $this->namespace;
        }

        // store value
        if (!is_null($this->tagValue)) {
            $result['value'] = $this->tagValue;
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

        // If is the root
        if (is_null($this->parent)) {
            return array($this->tagName => $result);
        }

        return $result;
    }

    /**
     * Returns a SOAP xml request
     *
     * @param  integer  $spaces
     * @param  integer  $currentTab
     * 
     * @return string
     */
    public function toXml($spaces = 0, $currentTab = 0)
    {
        // calc tab
        $tab = str_repeat(' ', $currentTab);
        $end = '';
        if ($spaces > 0) {
            $end = PHP_EOL;
        }

        // check namespace
        $ns = $this->namespace;

        if (empty($ns) && !is_null($this->parent)) {
            // Get ns from parent
            $ns = $this->parent->getNamespace();
            if (!empty($ns)) {
                $this->setNamespace($ns);
            }
        }

        // make tag
        if (empty($ns)) {
            $tag = $this->tagName;
        }
        else {
            $tag = $ns .':'. $this->tagName;
        }

        // Return empty tag
        if (empty($this->attrs) && empty($this->childs)) {
            return sprintf("%s<%s/>%s", $tab, $tag, $end);
        }

        $xml = sprintf('%s<%s', $tab, $tag);

        // Attributes
        if (count($this->attrs)) {
            $attrXml = array();
            foreach ($this->attrs as $nameAttr => $valueAttr) {
                $attrXml[] = sprintf('%s="%s"', $nameAttr, $valueAttr);
            }
            $xml .= ' '. implode(' ', $attrXml);
        }
        $xml .= '>';

        if (count($this->childs)) {
            $xml .= $end;

            // Childs
            $currentTab += $spaces;
            foreach ($this->childs as $nameChild => $childNode) {
                $xml .= $childNode->toXml($spaces, $currentTab);
            }

            $xml .= sprintf("%s</%s>%s", $tab, $tag, $end);
        }
        else {
            $xml .= sprintf("</%s>%s", $tag, $end);
        }

        return $xml;
    }
}