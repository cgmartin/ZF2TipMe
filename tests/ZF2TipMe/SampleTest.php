<?php
/**
 * ZF2TipMe
 *
 * @link      http://github.com/cgmartin/ZF2TipMe for the canonical source repository
 * @copyright Copyright (c) 2013 Christopher Martin (http://cgmartin.com)
 * @license   New BSD License https://raw.github.com/cgmartin/ZF2TipMe/master/LICENSE
 */

namespace ZF2TipMe;

class SampleTest extends Framework\TestCase
{
    public function testSample()
    {
        $this->assertInstanceOf('Zend\ServiceManager\ServiceLocatorInterface', $this->getLocator());
    }
}
