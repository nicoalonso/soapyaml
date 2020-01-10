<?php

namespace NK\SoapYaml\Tests;

use PHPUnit\Framework\TestCase;
use NK\SoapYaml\Node;

class NodeTest extends TestCase
{
    /**
     * Test load
     */
    public function testLoad()
    {
        // [ Given ]
        $data = array(
            'ns' => 'envelope',
            'query' => 'test',
            'keys' => array(
                'type' => 'string',
                'mandatory' => '',
                'readonly' => ''
            )
        );

        // [ When ]
        $node = new Node($data);

        // [ Then ]
        $this->assertEquals('envelope', $node->getNamespace());
        $this->assertEquals('test', $node->getAttr('query'));
        $this->assertNull($node->getAttr('dummy'));
        $subNode = $node->getChild('keys');
        $this->assertNotNull( $subNode );
        $this->assertEquals('envelope', $subNode->getNamespace());
        $this->assertEquals('string', $subNode->getAttr('type'));


    }
}
