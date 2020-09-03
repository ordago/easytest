<?php

namespace any_noniterable_arglist;

use easytest;


function setup_file() {
    return easytest\arglists(
        array(
            1,
            array(2, 3),
            4,
        )
    );
}

function teardown_file($args) {
    if ($args !== array(1, array(2, 3), 4)) {
        echo '$args = ', \print_r($arg, true);
    }
    echo '.';
}


function teardown_run($one, $two) {
    if (array(2, 3) !== array($one, $two)) {
        echo '$one = ', \print_r($one, true), "\n",
            '$two = ', \print_r($two, true);
    }
    echo '.';
}


function test_one($one, $two) {
    easytest\assert_identical(array(2, 3), array($one, $two));
}
