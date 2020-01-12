<?php

namespace NK\SoapYaml\Tests;

use PHPUnit\Framework\TestCase;
use NK\SoapYaml\Template;

/**
 * Test Node
 */
class TemplateTest extends TestCase
{
    /**
     * Test Load
     */
    public function testLoad()
    {
        // [ Given ]
        $template = new Template();
        $filename = __DIR__ . '/fixtures/load.yaml';
        $expect = file_get_contents(__DIR__. '/fixtures/expect_toXml_compact.xml');

        // [ When ]
        $res = $template->load( $filename );
        $request = $template->toXml();

        // [ Then ]
        $this->assertTrue( $res );
        $this->assertEquals($expect, $request);
    }

    /**
     * test updateValues
     */
    public function testUpdateValues()
    {
        // [ Given ]
        $template = new Template();
        $filename = __DIR__ . '/fixtures/load.yaml';
        $expect = file_get_contents(__DIR__. '/fixtures/expect_load_test1.xml');

        $data = array(
            'keys.query' => 'group',  // <-- Modify attribute
            'query' => array(
                'Category' => 'Users' // <-- Update recursive
            ),
            'fields' => array('id', 'name', 'date')  // Array type: setValue
        );
        $template->setData( $data );
        $template->load( $filename );

        // [ When ]
        $request = $template->toXml(2);

        // [ Then ]
        $this->assertEquals($expect, $request);
    }
}