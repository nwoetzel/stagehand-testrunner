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

namespace Stagehand\TestRunner\Core\DependencyInjection;

use Stagehand\ComponentFactory\UnfreezableContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ResolveParameterPlaceHoldersPass;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;

use Stagehand\TestRunner\Core\DependencyInjection\Extension\ExtensionRepository;

/**
 * @package    Stagehand_TestRunner
 * @copyright  2012 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 3.0.0
 */
class Compiler
{
    const COMPILED_CONTAINER_NAMESPACE = 'Stagehand\TestRunner\Core\DependencyInjection';
    const COMPILED_CONTAINER_CLASS = 'CompiledContainer';

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    protected $container;

    public function __construct()
    {
        $this->container = new UnfreezableContainerBuilder();
    }

    public function compile()
    {
        $this->compileContainer();
        $this->dumpContainer();
    }

    public function compileContainer()
    {
        foreach (ExtensionRepository::findAll() as $extension) {
            $this->container->registerExtension($extension);
        }

        foreach ($this->container->getExtensions() as $extension) { /* @var $extension \Symfony\Component\DependencyInjection\Extension\ExtensionInterface */
            $this->container->loadFromExtension($extension->getAlias(), array());
        }

        $this->container->getCompilerPassConfig()->setOptimizationPasses(array_filter($this->container->getCompilerPassConfig()->getOptimizationPasses(), function (CompilerPassInterface $compilerPass) {
            return !($compilerPass instanceof ResolveParameterPlaceHoldersPass);
        }));
        $this->container->compile();
    }

    public function dumpContainer()
    {
        $phpDumper = new PhpDumper($this->container);
        $compiledContainer = $phpDumper->dump(array(
            'class' => self::COMPILED_CONTAINER_CLASS
        ));

        $compiledContainer = preg_replace(
            '/^<\?php/',
            '<?php' . PHP_EOL . 'namespace ' . self::COMPILED_CONTAINER_NAMESPACE . ';' . PHP_EOL,
            $compiledContainer
        );

        $compiledContainerFile = __DIR__ . '/' . self::COMPILED_CONTAINER_CLASS . '.php';

        file_put_contents($compiledContainerFile, $compiledContainer);
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