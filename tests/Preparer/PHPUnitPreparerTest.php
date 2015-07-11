<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5.3
 *
 * Copyright (c) 2011-2013 KUBO Atsuhiro <kubo@iteman.jp>,
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    Stagehand_TestRunner
 * @copyright  2011-2013 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 2.19.0
 */

namespace Stagehand\TestRunner\Preparer;

use Stagehand\TestRunner\Test\PHPUnitComponentAwareTestCase;

/**
 * @package    Stagehand_TestRunner
 * @copyright  2011-2013 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 2.19.0
 */
class PHPUnitPreparerTest extends PHPUnitComponentAwareTestCase
{
    /**
     * @param boolean $colors
     * @test
     * @dataProvider booleans
     * @link http://redmine.piece-framework.com/issues/326
     */
    public function reflectsTheColorsAttributeInTheXmlConfigurationFileToTheConfiguration($colors)
    {
        $phpunitConfiguration = \Phake::mock('PHPUnit_Util_Configuration');
        \Phake::when($phpunitConfiguration)->getPHPUnitConfiguration()->thenReturn(array('colors' => $colors));
        $phpunitConfigurationFactory = \Phake::mock('Stagehand\TestRunner\DependencyInjection\PHPUnitConfigurationFactory');
        \Phake::when($phpunitConfigurationFactory)->create()->thenReturn($phpunitConfiguration);
        $this->setComponent('phpunit.phpunit_configuration_factory', $phpunitConfigurationFactory);
        $this->createComponent('preparer')->prepare();

        \Phake::verify($phpunitConfigurationFactory)->create();
        $terminal = $this->createComponent('terminal'); /* @var $terminal \Stagehand\TestRunner\CLI\Terminal */
        $this->assertEquals($colors, $terminal->shouldColor());
    }

    /**
     * @return array
     */
    public function booleans()
    {
        return array(array(true), array(false));
    }
}

/*
 * Local Variables:
 * mode: php
 * coding: utf-8
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * indent-tabs-mode: nil
 * End:
 */
