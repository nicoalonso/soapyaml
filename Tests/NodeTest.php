<?php

namespace NK\SoapYaml\Tests;

use PHPUnit\Framework\TestCase;
use NK\SoapYaml\Node;

/**
 * Test Node
 */
class NodeTest extends TestCase
{
    /**
     * Test add
     */
    public function testAdd()
    {
        // [ Given ]
        $node = new Node();

        // [ When ]
        $node->add('date', new \DateTime('now'));
        $node->add('query', array('type' => 'string'));
        $node->add(new Node('keys'));

        // [ Then ]
        $this->assertCount(1, $node->getAttrs());
        $this->assertCount(2, $node->getChilds());
        $this->assertNotNull( $node->get('date') );
        $this->assertNotNull( $node->getAttr('date') );
        $this->assertNotNull( $node->getDate() );
        $this->assertNotNull( $node->getKeys() );

        // [ When ]
        $node->getKeys()->add('query', 'dummy');

        // [ Then ]
        $this->assertEquals('dummy', $node->getChild('keys')->getAttr('query'));
    }

    /**
     * Test add exception wrong name
     * @expectedException NK\SoapYaml\Exception\LoadException
     */
    public function testAddWrongName()
    {
        // [ Given ]
        $node = new Node();

        // [ Then ]
        $node->add(123);
    }

    /**
     * Test add exception Node object
     * @expectedException NK\SoapYaml\Exception\LoadException
     */
    public function testAddWrongNodeObj()
    {
        // [ Given ]
        $node = new Node();

        // [ Then ]
        $node->add( new \DateTime('now') );
    }

    /**
     * Test load
     */
    public function testLoad()
    {
        // [ Given ]
        $data = array(
            'soap' => array(
                'ns' => 'envelope',
                '__attr__query' => 'test',  // make collision
                'keys' => array(
                    'type' => 'string',
                    'mandatory' => '',
                    'readonly' => ''
                ),
                '__child__query' => array(  // make collision
                    'type' => 'string',
                    'value' => 123
                )
            )
        );

        // [ When ]
        $node = new Node('soap', null, $data['soap']);

        // [ Then ]
        $this->assertEquals('envelope', $node->getNamespace());
        $this->assertEquals('soap', $node->getName());
        $this->assertCount(2, $node->getChilds());
        $this->assertEquals('test', $node->getAttr('query'));
        $this->assertNotNull($node->getChild('query'));
        $this->assertNotNull($node->get('query'));
        $this->assertNotNull($node->getQuery());
        $this->assertNull($node->getAttr('dummy'));
        $this->assertNull($node->getChild('dummy'));

        $subNode = $node->getChild('keys');
        $this->assertNotNull( $subNode );
        $this->assertEquals('envelope', $subNode->getNamespace());
        $this->assertEquals('string', $subNode->getAttr('type'));
        $this->assertEquals('string', $subNode->getType());
        $this->assertCount(3, $subNode->getAttrs());
        $this->assertEquals(123, $node->get('query')->getValue());
    }

    /**
     * Test __call
     * @expectedException NK\SoapYaml\Exception\LoadException
     */
    public function testCallMethodNotExists()
    {
        // [ Given ]
        $node = new Node();

        // [ When ]
        $node->dummy();
    }
}
