<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5.3.
 *
 * Copyright (c) 2010-2011 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2010-2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 *
 * @version    Release: @package_version@
 *
 * @since      File available since Release 2.11.2
 */

namespace Stagehand\TestRunner\JUnitXMLWriter;

use Stagehand\TestRunner\Util\SHString;

/**
 * @copyright  2010-2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 *
 * @version    Release: @package_version@
 *
 * @since      Class available since Release 2.11.2
 */
class XMLStreamWriter
{
    protected $buffer;
    protected $elements = array();
    protected $isStartTagClosed = true;

    public function __construct()
    {
        $this->buffer = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL;
    }

    public function startElement($element)
    {
        if (!$this->isStartTagClosed) {
            $this->closeStartTag();
        }

        $this->elements[] = $element;
        $this->buffer .= '<'.$element;
        $this->isStartTagClosed = false;
    }

    public function endElement()
    {
        if (!$this->isStartTagClosed) {
            $this->closeStartTag();
        }

        $currentElement = array_pop($this->elements);
        $this->buffer .= '</'.$currentElement.'>';
    }

    public function writeAttribute($attribute, $value)
    {
        $this->buffer .=
            ' '.
            $attribute.
            '="'.
            str_replace("\x0a", '&#10;', htmlspecialchars(SHString::normalizeNewlines($value), ENT_QUOTES, 'UTF-8')).
            '"';
    }

    public function text($text)
    {
        if (!$this->isStartTagClosed) {
            $this->closeStartTag();
        }

        $this->buffer .= htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }

    public function closeStartTag()
    {
        $this->buffer .= '>';
        $this->isStartTagClosed = true;
    }

    public function flush()
    {
        $buffer = $this->buffer;
        $this->buffer = '';

        return $buffer;
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
