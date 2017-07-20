<?php

// This file is part of Rogō
//
// Rogō is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogō is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogō.  If not, see <http://www.gnu.org/licenses/>.

require __DIR__ . '/../../../../classes/url.class.php';

class UrlTest extends \PHPUnit_Framework_TestCase {

    /**
     * @dataProvider fromGlobalsProvider
     * @param string $expected
     * @param array $server
     */
    public function testFromGlobals($expected, $server) {
        $url = \Url::fromGlobals($server);
        $this->assertSame($expected, (string) $url);
    }

    public function fromGlobalsProvider() {
        return array(
            array(
                'http://localhost/script.php',
                array('HTTP_HOST' => 'localhost', 'REQUEST_URI' => '/script.php')
            ),
            array(
                'http://localhost/script.php',
                array('HTTP_HOST' => 'localhost:80', 'REQUEST_URI' => '/script.php')
            ),
            array(
                'http://localhost/script.php',
                array('HTTP_HOST' => 'localhost:443', 'REQUEST_URI' => '/script.php')
            ),
            array(
                'http://localhost:8080/script.php',
                array('HTTP_HOST' => 'localhost:8080', 'REQUEST_URI' => '/script.php')
            ),
            array(
                'https://localhost/script.php',
                array('HTTP_HOST' => 'localhost', 'REQUEST_URI' => '/script.php', 'HTTPS' => 'on')
            ),
            array(
                'http://localhost/script.php?foo=bar',
                array('HTTP_HOST' => 'localhost', 'REQUEST_URI' => '/script.php?foo=bar')
            ),
        );
    }

    /**
     * @dataProvider getPartProvider
     * @param mixed $expected
     * @param string $url
     * @param string $name
     * @param mixed $default
     */
    public function testGetPart($expected, $url, $name, $default) {
        $this->assertSame($expected, (new Url($url))->getPart($name, $default));
    }

    public function getPartProvider() {
        return array(
            array('http', 'http://user:pass@localhost:8080/path?foo=bar#anchor', Url::SCHEME, null),
            array('user', 'http://user:pass@localhost:8080/path?foo=bar#anchor', Url::USER, null),
            array('pass', 'http://user:pass@localhost:8080/path?foo=bar#anchor', Url::PASS, null),
            array('localhost', 'http://user:pass@localhost:8080/path?foo=bar#anchor', Url::HOST, null),
            array(8080, 'http://user:pass@localhost:8080/path?foo=bar#anchor', Url::PORT, null),
            array('/path', 'http://user:pass@localhost:8080/path?foo=bar#anchor', Url::PATH, null),
            array('foo=bar', 'http://user:pass@localhost:8080/path?foo=bar#anchor', Url::QUERY, null),
            array('anchor', 'http://user:pass@localhost:8080/path?foo=bar#anchor', Url::FRAGMENT, null),
        );
    }

    public function testSetQueryValues() {
        $url = new Url('http://localhost/?foo=bar');
        $this->assertSame('foo=bar', $url->getQuery());
        
        $values = $url->getQueryAsArray();
        $this->assertArrayHasKey('foo', $values);
        
        $values['foo'] = 'baz';
        $values['amp'] = '&';
        
        $url->setQueryValues($values);
        $this->assertSame('foo=baz&amp=' . urlencode($values['amp']), $url->getQuery());
    }
}
