<?php
// This file is part of EasyTest. It is subject to the license terms in the
// LICENSE.txt file found in the top-level directory of this distribution.
// No part of this project, including this file, may be copied, modified,
// propagated, or distributed except according to the terms contained in the
// LICENSE.txt file.

namespace easytest;


// These function comprise the API to generate EasyTest-specific exceptions

function skip($reason) {
    throw new Skip($reason);
}


function fail($reason) {
    throw new Failure($reason);
}



// Implementation


final class Error extends \ErrorException {

    public function __construct($message, $severity, $file, $line) {
        parent::__construct($message, 0, $severity, $file, $line);
    }

    public function __toString() {
        if (!$this->string) {
            $this->string =  \sprintf(
                "%s\nin %s on line %s\n\nStack trace:\n%s",
                $this->message,
                $this->file,
                $this->line,
                $this->getTraceAsString()
            );
        }
        return $this->string;
    }

    private $string;
}



// #BC(5.6): Extend Failure from Exception
if (\version_compare(\PHP_VERSION, '7.0', '<')) {

    final class Failure extends \Exception {

        public function __construct($message) {
            parent::__construct($message);
            list($this->file, $this->line, $this->trace)
                = namespace\_find_client_call_site();
        }


        public function __toString() {
            if (!$this->string) {
                $this->string = namespace\_format_exception_string(
                    "%s\n\nin %s on line %s",
                    $this->message, $this->file, $this->line, $this->trace
                );
            }
            return $this->string;
        }

        private $string;
        private $trace;
    }

}
else {

    final class Failure extends \AssertionError {

        public function __construct($message) {
            parent::__construct($message);
            list($this->file, $this->line, $this->trace)
                = namespace\_find_client_call_site();
        }


        public function __toString() {
            if (!$this->string) {
                $this->string = namespace\_format_exception_string(
                    "%s\n\nin %s on line %s",
                    $this->message, $this->file, $this->line, $this->trace
                );
            }
            return $this->string;
        }

        private $string;
        private $trace;
    }

}



final class Skip extends \Exception {

    public function __construct($message) {
        parent::__construct($message);
        list($this->file, $this->line, $this->trace)
            = namespace\_find_client_call_site();
    }


    public function __toString() {
        if (!$this->string) {
            $this->string = namespace\_format_exception_string(
                "%s\nin %s on line %s",
                $this->message, $this->file, $this->line, $this->trace
            );
        }
        return $this->string;
    }

    private $string;
    private $trace;
}



function _find_client_call_site() {
    // Find the first call in a backtrace that's outside of easytest
    // #BC(5.3): Pass false for debug_backtrace() $option parameter
    $trace = \version_compare(\PHP_VERSION, '5.3.6', '<')
           ? \debug_backtrace(false)
           : \debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS);
    for($i = 0, $c = \count($trace); $i < $c; ++$i) {
        // Apparently there's no file if we were thrown from the error
        // handler
        if (isset($trace[$i]['file'])
            && __DIR__ !== \dirname($trace[$i]['file']))
        {
            break;
        }
    }

    return [
        $trace[$i]['file'],
        $trace[$i]['line'],
        // Advance the trace index ($i) so the trace array provides a backtrace
        // from the call site
        \array_slice($trace, $i + 1),
    ];
}


function _format_exception_string($format, $message, $file, $line, $trace) {
    $string = \sprintf($format, $message, $file, $line);

    // Create a backtrace excluding calls made within easytest
    $buffer = [];
    for($i = 0, $c = \count($trace); $i < $c; ++$i) {
        $line = $trace[$i];
        if (__DIR__ === \dirname($line['file'])) {
            // We don't want to walk the entire call stack, because easytest's
            // entry point is probably outside the easytest directory, and we
            // don't want to erroneously show that as a client call. We need a
            // checkpoint so, once we hit it, we know we can't be in client
            // code anymore. It seems "discover_tests" is the lowest we can set
            // that checkpoint, as clients can throw exceptions in a variety of
            // places (e.g., setup fixtures) all of which are subsumed by
            // discover_tests
            if ('easytest\\discover_tests' === $line['function']) {
                break;
            }
            continue;
        }

        $callee = $line['function'];
        if (isset($line['class'])) {
            $callee = \sprintf(
                '%s%s%s',
                $line['class'], $line['type'], $callee
            );
        }

        $buffer[] = \sprintf('%s(%d): %s()',
            $line['file'],
            $line['line'],
            $callee
        );
    }
    if ($buffer) {
        $string = \sprintf(
            "%s\n\nCalled from:\n%s",
            $string, \implode("\n", $buffer)
        );
    }

    return $string;
}
