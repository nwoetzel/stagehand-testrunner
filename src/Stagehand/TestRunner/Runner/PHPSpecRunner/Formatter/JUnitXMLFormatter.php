<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5.3
 *
 * Copyright (c) 2012 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2012 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 3.0.0
 */

namespace Stagehand\TestRunner\Runner\PHPSpecRunner\Formatter;

use PHPSpec\Runner\Formatter\Progress;
use PHPSpec\Runner\ReporterEvent;

use Stagehand\TestRunner\Core\TestTargets;
use Stagehand\TestRunner\JUnitXMLWriter\JUnitXMLWriter;
use Stagehand\TestRunner\TestSuite\PHPSpecTestSuite;
use Stagehand\TestRunner\Util\FailureTrace;

/**
 * @package    Stagehand_TestRunner
 * @copyright  2012 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 3.0.0
 */
class JUnitXMLFormatter extends Progress
{
    /**
     * @var string
     */
    private static $STATUS_PASS = '.';

    /**
     * @var string
     */
    private static $STATUS_FAILURE = 'F';

    /**
     * @var string
     */
    private static $STATUS_ERROR = 'E';

    /**
     * @var string
     */
    private static $STATUS_PENDING = '*';

    /**
     * @var \Stagehand\TestRunner\JUnitXMLWriter\JUnitXMLWriter
     */
    protected $junitXMLWriter;

    /**
     * @var \Stagehand\TestRunner\TestSuite\PHPSpecTestSuite
     */
    protected $testSuite;

    /**
     * @var \Stagehand\TestRunner\Core\TestTargets
     */
    protected $testTargets;

    /**
     * @var string
     */
    protected $currentExampleGroupName;

    /**
     * @var integer
     */
    protected $exampleStartTime;

    /**
     * @param \Stagehand\TestRunner\JUnitXMLWriter\JUnitXMLWriter $junitXMLWriter
     */
    public function setJUnitXMLWriter(JUnitXMLWriter $junitXMLWriter)
    {
        $this->junitXMLWriter = $junitXMLWriter;
    }

    /**
     * @param \Stagehand\TestRunner\TestSuite\PHPSpecTestSuite $testSuite
     */
    public function setTestSuite(PHPSpecTestSuite $testSuite)
    {
        $this->testSuite = $testSuite;
    }

    /**
     * @param \Stagehand\TestRunner\Core\TestTargets $testTargets
     */
    public function setTestTargets(TestTargets $testTargets)
    {
        $this->testTargets = $testTargets;
    }

    /**
     * @throws \Stagehand\TestRunner\Runner\PHPSpecRunner\Formatter\UnknownStatusException
     * @throws \Stagehand\TestRunner\TestSuite\PHPSpecTestSuite\ExampleGroupNotFoundException
     * @throws \Stagehand\TestRunner\TestSuite\PHPSpecTestSuite\ExampleNotFoundException
     */
    public function update(\SplSubject $method, $reporterEvent = null)
    {
        if ($reporterEvent->event == 'exampleStart') {
            $this->startRenderingExample($reporterEvent);
        } elseif ($reporterEvent->event == 'exampleFinish') {
            $this->finishRenderingExample($reporterEvent);
        } else {
            parent::update($method, $reporterEvent);
        }
    }

    public function output()
    {
    }

    /**
     * @throws \Stagehand\TestRunner\TestSuite\PHPSpecTestSuite\ExampleGroupNotFoundException
     */
    protected function _startRenderingExampleGroup($reporterEvent)
    {
        if (is_null($this->currentExampleGroupName)) {
            $this->junitXMLWriter->startTestSuites();
            $this->junitXMLWriter->startTestSuite(
                $this->testSuite->getName(),
                $this->testSuite->getAllExampleCount()
            );
        }

        $this->currentExampleGroupName = $reporterEvent->example;
        $this->junitXMLWriter->startTestSuite(
            $this->testSuite->getExampleGroupClass($this->currentExampleGroupName),
            $this->testSuite->getExampleCount($this->currentExampleGroupName)
        );
    }

    protected function _finishRenderingExampleGroup()
    {
        $this->junitXMLWriter->endTestSuite();
    }

    /**
     * @throws \Stagehand\TestRunner\Runner\PHPSpecRunner\Formatter\UnknownStatusException
     */
    protected function _renderExamples($reporterEvent)
    {
        switch ($reporterEvent->status) {
        case self::$STATUS_PASS:
            break;
        case self::$STATUS_FAILURE:
            $this->renderFailure($reporterEvent);
            break;
        case self::$STATUS_ERROR:
            $this->renderError($reporterEvent);
            break;
        case self::$STATUS_PENDING:
            $this->renderPending($reporterEvent);
            break;
        default:
            throw new UnknownStatusException('An unknown status [ ' . $reporterEvent->status . ' ] is given for the example corresponding to [ ' . $reporterEvent->example . ' ] and [ ' . $this->currentExampleGroupName .  ' ].');
        }
    }

    /**
     * @param \PHPSpec\Runner\ReporterEvent $reporterEvent
     */
    protected function renderFailure(ReporterEvent $reporterEvent)
    {
        $this->renderFailureOrError($reporterEvent, 'failure');
    }

    /**
     * @param \PHPSpec\Runner\ReporterEvent $reporterEvent
     */
    protected function renderError(ReporterEvent $reporterEvent)
    {
        $this->renderFailureOrError($reporterEvent, 'error');
    }

    /**
     * @param \PHPSpec\Runner\ReporterEvent $reporterEvent
     * @param string $failureOrError
     */
    protected function renderFailureOrError(ReporterEvent $reporterEvent, $failureOrError)
    {
        list($file, $line) = FailureTrace::findFileAndLineOfFailureOrError(
            $this->testTargets->getRequiredSuperTypes(),
            $reporterEvent->exception,
            new \ReflectionClass($this->testSuite->getExampleGroupClass($this->currentExampleGroupName))
        );
        $failureTrace = FailureTrace::buildFailureTrace($reporterEvent->exception->getTrace());
        $this->junitXMLWriter->{ 'write' . $failureOrError }(
            $reporterEvent->message . PHP_EOL . PHP_EOL . $failureTrace,
            get_class($reporterEvent->exception),
            $file,
            $line,
            $reporterEvent->message
        );
    }

    /**
     * @param \PHPSpec\Runner\ReporterEvent $reporterEvent
     */
    protected function renderPending(ReporterEvent $reporterEvent)
    {
        $this->junitXMLWriter->writeError($reporterEvent->message, null, null, null, $reporterEvent->message);
    }

    protected function _onExit()
    {
        $this->junitXMLWriter->endTestSuite();
        $this->junitXMLWriter->endTestSuites();
    }

    /**
     * @param \PHPSpec\Runner\ReporterEvent $reporterEvent
     * @throws \Stagehand\TestRunner\TestSuite\PHPSpecTestSuite\ExampleGroupNotFoundException
     * @throws \Stagehand\TestRunner\TestSuite\PHPSpecTestSuite\ExampleNotFoundException
     */
    protected function startRenderingExample(ReporterEvent $reporterEvent)
    {
        $this->junitXMLWriter->startTestCase(
            $reporterEvent->example,
            $this->testSuite->getExampleGroupClass($this->currentExampleGroupName),
            $this->testSuite->getExampleMethod(
                $this->currentExampleGroupName,
                $reporterEvent->example
            )
        );
        $this->exampleStartTime = microtime(true);
    }

    protected function finishRenderingExample($reporterEvent)
    {
        $elapsedTime = microtime(true) - $this->exampleStartTime;
        $this->junitXMLWriter->endTestCase($elapsedTime);
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
