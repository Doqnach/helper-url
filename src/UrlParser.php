<?php
    /**
     * @author Doqnach
     * @link http://miraizou.net
     * @copyright Miraizou.net : Vision of the Future
     * @license http://www.opensource.org/licenses/mit-license.php MIT License
     */

    declare(strict_types=1);

    namespace Miraizou\Helper;

    /**
     * Basic URL parser class
     *
     * @see https://tools.ietf.org/html/rfc2732
     * @see https://mathiasbynens.be/demo/url-regex
     * @see http://home.deds.nl/~aeron/regex/
     * @see https://regex101.com/r/m0WJAK/10 by Doqnach
     *
     * @package Miraizou\Helper
     */
    class UrlParser
    {
        private const _URL_REGEX = <<<'REGEXP'
~
(?(DEFINE)
  (?<ipv4>(?:2[0-4]|1\d|[1-9])?\d|25[0-5])
  (?<ipv6>
    \[(?:((?=.*(::))(?!.*\4.+\4))\4?|([\dA-F]{1,4}(?:\4|:\b|(?=\]))|\3))(?5){5}(?:(?5){2}|(?&ipv4)(?:.(?&ipv4)){3})\]
  )
)
^
(?=.)
(?!///?.?$)
(?<section_host>
  (?:
    (?<section_scheme>(?<scheme>https?)://|//)
    (?<section_creds>(?<user>[^@:\s]+?)(?::(?<pass>[^@\s]+?))?@)?
  )?
  (?<host>
    # host - ipv4
    (?!10(?:\.\d{1,3}){3})
    (?!127(?:\.\d{1,3}){3})
    (?!169\.254(?:\.\d{1,3}){2})
    (?!192\.168(?:\.\d{1,3}){2})
    (?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})
    (?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])
    (?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}
    (?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))
    # ipv6
    |(?&ipv6)
    # host
    |
    (?:(?:[a-z\x{00a1}-\x{ffff}0-9]+[-_]?)*[a-z\x{00a1}-\x{ffff}0-9]+)
    (?:\.(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)*
    (?:\.(?:[a-z\x{00a1}-\x{ffff}]{2,}))
  )
  (?::(?<port>(?:[1-9]\d{0,3}|[1-5]\d{4}|6[1-4]\d{3}|65(?:[0-4]\d{2}|5(?:[0-3]\d|3[1-5])))))?
)?
(?<path>
  /[^\s?#]*?
)?
(?:
  \?(?<query>[^\s#]*?)
)?
(?:
  \#(?<fragment>.*)
)?
$
~xiu
REGEXP;

        /** @var string[] */
        private $_components = [
          PHP_URL_SCHEME => null,
          PHP_URL_USER => null,
          PHP_URL_PASS => null,
          PHP_URL_HOST => null,
          PHP_URL_PORT => null,
          PHP_URL_PATH => null,
          PHP_URL_QUERY => null,
          PHP_URL_FRAGMENT => null,
        ];

        /**
         * @param string $url
         * @throws UrlParserException
         */
        public function __construct(string $url)
        {
            if (false === $this->_parse($url)) {
                throw new UrlParserException('Invalid URL Syntax: ' . $url);
            }
        }

        /**
         * @param string $url
         * @return bool
         */
        private function _parse(string $url) : bool
        {
            if (!(\preg_match(self::_URL_REGEX, $url, $components) > 0)) {
                return false;
            }

            if (true === \array_key_exists('host', $components) && false === empty($components['host'])) {
                if (true === \array_key_exists('scheme', $components) && false === empty($components['scheme'])) {
                    $this->_components[PHP_URL_SCHEME] = $components['scheme'];
                }
                if (true === \array_key_exists('user', $components) && false === empty($components['user'])) {
                    $this->_components[PHP_URL_USER] = $components['user'];
                }
                if (true === \array_key_exists('pass', $components) && false === empty($components['pass'])) {
                    $this->_components[PHP_URL_PASS] = $components['pass'];
                }
                $this->_components[PHP_URL_HOST] = $components['host'];
                if (true === \array_key_exists('port', $components) && false === empty($components['port'])) {
                    $this->_components[PHP_URL_PORT] = $components['port'];
                }
            }
            if (true === \array_key_exists('path', $components) && false === empty($components['path'])) {
                $this->_components[PHP_URL_PATH] = $components['path'];
            }
            if (true === \array_key_exists('query', $components) && false === empty($components['query'])) {
                $this->_components[PHP_URL_QUERY] = $components['query'];
            }
            if (true === \array_key_exists('fragment', $components) && false === empty($components['fragment'])) {
                $this->_components[PHP_URL_FRAGMENT] = $components['fragment'];
            }

            return !(\count(\array_filter($this->_components, function ($v) { return false === empty($v); })) === 0);
        }

        /**
         * Get a single component, or null if given component is not present or for an invalid component identifier
         *
         * @param int $component one of PHP_URL_*
         * @return null|string returns null if given component does not exist, or string otherwise
         */
        public function getComponent(int $component) : ?string
        {
            if (false === \array_key_exists($component, $this->_components)) {
                return null;
            }

            return $this->_components[$component];
        }

        /**
         * Get all components, where non-present are null
         *
         * @return string[] array index is one of PHP_URL_*
         */
        public function getComponents() : array
        {
            return $this->_components;
        }

        /**
         * Get the URL scheme or null if not present
         *
         * @return null|string
         */
        public function getScheme() : ?string
        {
            return $this->getComponent(PHP_URL_SCHEME);
        }

        /**
         * Get the URL basic authentication credentials username or null if not present
         *
         * @return null|string
         */
        public function getUsername() : ?string
        {
            return $this->getComponent(PHP_URL_USER);
        }

        /**
         * Get the URL basic authentication credentials password or null if not present
         *
         * @return null|string
         */
        public function getPassword() : ?string
        {
            return $this->getComponent(PHP_URL_PASS);
        }

        /**
         * Get the URL hostname, IPv4, or IPv6 (including []-brackets) or null if not present
         * @return null|string
         */
        public function getHost() : ?string
        {
            return $this->getComponent(PHP_URL_HOST);
        }

        /**
         * Get the URL port as integer or null if not present
         *
         * @return int|null
         */
        public function getPort() : ?int
        {
            $port = $this->getComponent(PHP_URL_PORT);
            return null === $port ? null : (int)$port;
        }

        /**
         * Get the URL path or null if not present
         *
         * @return null|string
         */
        public function getPath() : ?string
        {
            return $this->getComponent(PHP_URL_PATH);
        }

        /**
         * Get the URL Query String (without ?) or null if not present
         *
         * @return null|string
         */
        public function getQuery() : ?string
        {
            return $this->getComponent(PHP_URL_QUERY);
        }

        /**
         * Get the URL fragment (without #) or null if not present
         *
         * @return null|string
         */
        public function getFragment() : ?string
        {
            return $this->getComponent(PHP_URL_FRAGMENT);
        }

        /**
         * @return string
         */
        public function __toString() : string
        {
            $url = '';

            if (null !== $this->_components[PHP_URL_SCHEME]) {
                $url .= $this->_components[PHP_URL_SCHEME] . '://';
            }
            if (null !== $this->_components[PHP_URL_HOST]) {
                if (null !== $this->_components[PHP_URL_USER]) {
                    $url .= $this->_components[PHP_URL_USER];
                    if (null !== $this->_components[PHP_URL_PASS]) {
                        $url .= ':' . $this->_components[PHP_URL_PASS];
                    }
                    $url .= '@';
                }
                $url .= $this->_components[PHP_URL_HOST];
                if (null !== $this->_components[PHP_URL_PORT]) {
                    $url .= ':' . (int)$this->_components[PHP_URL_PORT];
                }
            }
            $url .= $this->_components[PHP_URL_PATH];
            if (null !== $this->_components[PHP_URL_QUERY]) {
                $url .= '?' . $this->_components[PHP_URL_QUERY];
            }
            if (null !== $this->_components[PHP_URL_FRAGMENT]) {
                $url .= '#' . $this->_components[PHP_URL_FRAGMENT];
            }

            return $url;
        }
    }
