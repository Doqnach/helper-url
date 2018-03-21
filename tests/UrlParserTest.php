<?php
    /**
     * @author Doqnach
     * @link http://miraizou.net
     * @copyright Miraizou.net : Vision of the Future
     * @license http://www.opensource.org/licenses/mit-license.php MIT License
     */

    declare(strict_types=1);

    use Miraizou\Helper\UrlParser;
    use Miraizou\Helper\UrlParserException;
    use PHPUnit\Framework\TestCase;
    use Symfony\Component\Yaml\Yaml;

    class UrlParserTest extends TestCase
    {
        /**
         * Example full URL to test against
         *
         * @var string
         */
        private const _VALID_FULL_URL = 'https://user:pass@domain.tld:443/foo/bar?baz=meh&bur#frag;kek';

        /**
         * Example full URL split into its components to test against
         *
         * @var string
         */
        private const _VALID_FULL_URL_COMPONENTS = [
          PHP_URL_SCHEME => 'https',
          PHP_URL_USER => 'user',
          PHP_URL_PASS => 'pass',
          PHP_URL_HOST => 'domain.tld',
          PHP_URL_PORT => 443,
          PHP_URL_PATH => '/foo/bar',
          PHP_URL_QUERY => 'baz=meh&bur',
          PHP_URL_FRAGMENT => 'frag;kek',
        ];

        /**
         * Read all valid URLs
         *
         * @return string[]
         */
        public function validUrlProvider() : array
        {
            return Yaml::parseFile(__DIR__ . DIRECTORY_SEPARATOR . 'urls.yml')['valid'];
        }

        /**
         * Read all invalid URLs
         *
         * @return string[]
         */
        public function invalidUrlProvider() : array
        {
            return Yaml::parseFile(__DIR__ . DIRECTORY_SEPARATOR . 'urls.yml')['invalid'];
        }

        /**
         * Test if all supplied URLs are parsed as valid and if the __toString() does its job
         *
         * @dataProvider validUrlProvider
         * @param string $url
         */
        public function testValidURL(string $url) : void
        {
            $parser = new UrlParser($url);
            //\var_dump($url, $parser);
            $this->assertEquals((string)$parser, $url);
        }

        /**
         * Test each individual component, and all components as array
         */
        public function testComponents() : void
        {
            $parser = new UrlParser(self::_VALID_FULL_URL);

            $this->assertEquals(self::_VALID_FULL_URL_COMPONENTS[PHP_URL_SCHEME], $parser->getScheme(), 'Invalid scheme');
            $this->assertEquals(self::_VALID_FULL_URL_COMPONENTS[PHP_URL_USER], $parser->getUsername(), 'Invalid user');
            $this->assertEquals(self::_VALID_FULL_URL_COMPONENTS[PHP_URL_PASS], $parser->getPassword(), 'Invalid pass');
            $this->assertEquals(self::_VALID_FULL_URL_COMPONENTS[PHP_URL_HOST], $parser->getHost(), 'Invalid host');
            $this->assertEquals(self::_VALID_FULL_URL_COMPONENTS[PHP_URL_PORT], $parser->getPort(), 'Invalid port');
            $this->assertEquals(self::_VALID_FULL_URL_COMPONENTS[PHP_URL_PATH], $parser->getPath(), 'Invalid path');
            $this->assertEquals(self::_VALID_FULL_URL_COMPONENTS[PHP_URL_QUERY], $parser->getQuery(), 'Invalid query string');
            $this->assertEquals(self::_VALID_FULL_URL_COMPONENTS[PHP_URL_FRAGMENT], $parser->getFragment(), 'Invalid fragment');

            $this->assertEquals(self::_VALID_FULL_URL_COMPONENTS, $parser->getComponents());
        }

        /**
         * Check if all supplied URLs are parsed as invalid, throwing an exception
         *
         * @dataProvider invalidUrlProvider
         * @param string $url
         */
        public function testInvalidURL(string $url) : void
        {
            $this->expectException(UrlParserException::class);
            new UrlParser($url);
            $this->fail('Valid url syntax: ' . $url);
        }
    }
