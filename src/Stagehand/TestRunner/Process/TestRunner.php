<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5.3
 *
 * Copyright (c) 2010-2011 KUBO Atsuhiro <kubo@iteman.jp>,
 *               2011 Shigenobu Nishikawa <shishi.s.n@gmail.com>,
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
 * @copyright  2010-2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @copyright  2011 Shigenobu Nishikawa <shishi.s.n@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 2.14.0
 */

namespace Stagehand\TestRunner\Process;

use Stagehand\TestRunner\Collector\CollectorFactory;
use Stagehand\TestRunner\Config;
use Stagehand\TestRunner\Notification\Notifier;
use Stagehand\TestRunner\Preparer\PreparerFactory;
use Stagehand\TestRunner\Runner\RunnerFactory;

/**
 * @package    Stagehand_TestRunner
 * @copyright  2010-2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @copyright  2011 Shigenobu Nishikawa <shishi.s.n@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 2.14.0
 */
class TestRunner
{
    /**
     * @var \Stagehand\TestRunner\Config
     */
    protected $config;

    /**
     * @var boolean $result
     * @since Property available since Release 2.18.0
     */
    protected $result;

    /**
     * @param \Stagehand\TestRunner\Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Runs tests.
     *
     * @since Method available since Release 2.1.0
     */
    public function run()
    {
        $this->createPreparer()->prepare();

        $runner = $this->createRunner();
        $this->result = $runner->run($this->createCollector()->collect());

        if ($this->config->usesNotification) {
            $this->createNotifier()->notifyResult($runner->getNotification());
        }
    }

    /**
     * @return \Stagehand\TestRunner\Preparer\Preparer
     * @since Method available since Release 2.12.0
     */
    protected function createPreparer()
    {
        $factory = new PreparerFactory($this->config);
        return $factory->create();
    }

    /**
     * @return \Stagehand\TestRunner\Collector\Collector
     * @since Method available since Release 2.11.0
     */
    protected function createCollector()
    {
        $factory = new CollectorFactory($this->config);
        return $factory->create();
    }

    /**
     * @return \Stagehand\TestRunner\Runner\Runner
     * @since Method available since Release 2.11.0
     */
    protected function createRunner()
    {
        $factory = new RunnerFactory($this->config);
        return $factory->create();
    }

    /**
     * @return \Stagehand\TestRunner\Notification\Notifier
     * @since Method available since Release 2.18.0
     */
    protected function createNotifier()
    {
        return new Notifier();
    }
}

/*
 * Local Variables:
 * mode: php
 * coding: iso-8859-1
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * indent-tabs-mode: nil
 * End:
 */