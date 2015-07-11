<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5.3.
 *
 * Copyright (c) 2011-2012 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2011-2012 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 *
 * @version    Release: @package_version@
 *
 * @since      File available since Release 3.0.0
 */
namespace Stagehand\TestRunner\Core\Plugin;

use Symfony\Component\Finder\Finder;

/**
 * @copyright  2011-2012 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 *
 * @version    Release: @package_version@
 *
 * @since      Class available since Release 3.0.0
 */
class PluginRepository
{
    /**
     * @var array
     */
    private static $plugins;

    /**
     * @return array
     */
    public static function findAll()
    {
        if (is_null(self::$plugins)) {
            self::loadAllPlugins();
        }

        return self::$plugins;
    }

    /**
     * @param string $pluginID
     *
     * @return \Stagehand\TestRunner\Core\Plugin\PluginInterface
     */
    public static function findByPluginID($pluginID)
    {
        if (is_null(self::$plugins)) {
            self::loadAllPlugins();
        }

        foreach (self::$plugins as $plugin) { /* @var $plugin \Stagehand\TestRunner\Core\Plugin\PluginInterface */
            if (strtolower($plugin->getPluginID()) == strtolower($pluginID)) {
                return $plugin;
            }
        }
    }

    private static function loadAllPlugins()
    {
        foreach (Finder::create()->name('/^.+Plugin\.php$/')->files()->in(__DIR__) as $file) { /* @var $file \SplFileInfo */
            $pluginClass = new \ReflectionClass(__NAMESPACE__.'\\'.$file->getBasename('.php'));
            if (!$pluginClass->isInterface()
                && !$pluginClass->isAbstract()
                && $pluginClass->isSubclassOf('Stagehand\TestRunner\Core\Plugin\PluginInterface')) {
                self::$plugins[] = $pluginClass->newInstance();
            }
        }
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
