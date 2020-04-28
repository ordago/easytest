<?php
// This file is part of EasyTest. It is subject to the license terms in the
// LICENSE.txt file found in the top-level directory of this distribution.
// No part of this project, including this file, may be copied, modified,
// propagated, or distributed except according to the terms contained in the
// LICENSE.txt file.

namespace easytest;


// The functions in this file comprise EasyTest's assertion API

function assert_throws($expected, $callback, $description = null) {
    try {
        $callback();
    }
    catch (\Throwable $e) {}
    // #BC(5.6): Catch Exception
    catch (\Exception $e) {}

    if (!isset($e)) {
        $message = namespace\format_failure_message(
            "Expected to catch $expected but no exception was thrown",
            $description
        );
        throw new Failure($message);
    }

    if ($e instanceof $expected) {
        return $e;
    }

    $message = \sprintf(
        'Expected to catch %s but instead caught %s',
        $expected, \get_class($e)
    );
    throw new \Exception($message, 0, $e);
}


function assert_equal($expected, $actual, $description = null) {
    if ($expected == $actual) {
        return;
    }

    if (\is_array($expected) && \is_array($actual)) {
        namespace\ksort_recursive($expected);
        namespace\ksort_recursive($actual);
    }
    $message = namespace\format_failure_message(
        'Assertion "$expected == $actual" failed',
        $description,
        namespace\diff(
            namespace\format_variable($expected),
            namespace\format_variable($actual),
            'expected', 'actual'
        )
    );
    throw new Failure($message);
}


function assert_identical($expected, $actual, $description = null) {
    if ($expected === $actual) {
        return;
    }

    $message = namespace\format_failure_message(
        'Assertion "$expected === $actual" failed',
        $description,
        namespace\diff(
            namespace\format_variable($expected),
            namespace\format_variable($actual),
            'expected', 'actual'
        )
    );
    throw new Failure($message);
}
