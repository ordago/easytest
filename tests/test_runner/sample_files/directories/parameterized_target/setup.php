<?php

namespace param_target;

use easytest;


function setup_directory() {
    echo __DIR__;
    return easytest\arglists(array(array(1), array(2)));
}


function teardown_directory($arglists) {
    echo __DIR__;
}