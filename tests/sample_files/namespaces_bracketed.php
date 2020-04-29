<?php

namespace ns1\ns1 {

class TestNamespace {
    public function test() {}
}

/* ensure the namespace operator isn't confused for a namespace declaration */
const FOO = 'foo';
namespace\FOO;


function TestNamespace() {}


}

namespace
    ns1 // parent namespace
    \   // namespace separator
    ns2 // sub namespace
{

class TestNamespace {
    public function test() {}
}


function TestNamespace() {}


const FOO = 'foo';
namespace\FOO;

}

namespace { // global namespace


function TestNamespace() {}


class TestNamespace {
    public function test() {}
}

const FOO = 'foo';
namespace\FOO;

}
