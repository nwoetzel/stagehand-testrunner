<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5.3.
 *
 * Copyright (c) 2007-2012, 2014-2015 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2007-2012, 2014-2015 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 *
 * @version    Release: @package_version@
 *
 * @link       http://www.phpunit.de/
 * @since      File available since Release 1.2.0
 */
namespace Stagehand\TestRunner\Runner\PHPUnitRunner\Printer;

use Stagehand\TestRunner\Notification\Notification;
use Stagehand\TestRunner\Runner\PHPUnitRunner\TestDox\TestDox;
use Stagehand\TestRunner\Runner\Runner;

/**
 * A result printer for PHPUnit.
 *
 * @copyright  2007-2012, 2014-2015 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 *
 * @version    Release: @package_version@
 *
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 1.2.0
 */
class ResultPrinter extends \PHPUnit_TextUI_ResultPrinter
{
    /**
     * @var \Stagehand\TestRunner\Runner\Runner
     *
     * @since Property available since Release 3.0.0
     */
    protected $runner;

    /**
     * @var \Stagehand\TestRunner\Notification\Notification
     *
     * @since Property available since Release 3.0.0
     */
    protected $notification;

    /**
     * @param \PHPUnit_Framework_TestResult $result
     */
    public function printResult(\PHPUnit_Framework_TestResult $result)
    {
        if ($this->runner->shouldNotify()) {
            ob_start();
        }

        $testDox = trim(TestDox::get(spl_object_hash($result)));
        if (strlen($testDox)) {
            $this->write(PHP_EOL.PHP_EOL.$testDox);
        }

        parent::printResult($result);

        if ($this->runner->shouldNotify()) {
            $output = ob_get_contents();
            ob_end_clean();

            echo $output;

            if ($result->failureCount() + $result->errorCount() + $result->skippedCount() + $result->notImplementedCount() == 0) {
                $notificationResult = Notification::RESULT_PASSED;
            } else {
                $notificationResult = Notification::RESULT_FAILED;
            }

            $output = $this->removeAnsiEscapeCodesForColors($output);
            if (preg_match('/(OK \(\d+ tests?, \d+ assertions?\))/', $output, $matches)) {
                $notificationMessage = $matches[1];
            } elseif (preg_match('/(FAILURES!)\s+(.*)/', $output, $matches)) {
                $notificationMessage = $matches[1].PHP_EOL.$matches[2];
            } elseif (preg_match('/(OK, but incomplete,.*!)\s+(.*)/', $output, $matches)) {
                $notificationMessage = $matches[1].PHP_EOL.$matches[2];
            } elseif (preg_match('/(No tests executed!)/', $output, $matches)) {
                $notificationMessage = $matches[1];
            } else {
                $notificationMessage = '';
            }

            $this->notification = new Notification($notificationResult, $notificationMessage);
        }
    }

    /**
     * @param \PHPUnit_Framework_TestSuite $suite
     *
     * @since Method available since Release 2.7.0
     */
    public function startTestSuite(\PHPUnit_Framework_TestSuite $suite)
    {
        $oldVerbose = $this->verbose;
        $this->verbose = false;
        parent::startTestSuite($suite);
        $this->verbose = $oldVerbose;
    }

    /**
     * @param \PHPUnit_Framework_TestSuite $suite
     *
     * @since Method available since Release 2.7.0
     */
    public function endTestSuite(\PHPUnit_Framework_TestSuite $suite)
    {
        $oldVerbose = $this->verbose;
        $this->verbose = false;
        parent::endTestSuite($suite);
        $this->verbose = $oldVerbose;
    }

    /**
     * @param \Stagehand\TestRunner\Runner\Runner $runner
     *
     * @since Method available since Release 3.0.0
     */
    public function setRunner(Runner $runner)
    {
        $this->runner = $runner;
    }

    /**
     * @return \Stagehand\TestRunner\Notification\Notification
     *
     * @since Method available since Release 3.0.0
     */
    public function getNotification()
    {
        return $this->notification;
    }

    /**
     * @param string $text
     *
     * @return string
     *
     * @since Method available since Release 4.1.0
     */
    private function removeAnsiEscapeCodesForColors($text)
    {
        return preg_replace('/\x1b\[\d+(?:;\d+)?m/', '', $text);
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
