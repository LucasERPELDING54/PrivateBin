<?php
/**
 * PrivateBin.
 *
 * a zero-knowledge paste bin
 *
 * @see      https://github.com/PrivateBin/PrivateBin
 *
 * @copyright 2012 Sébastien SAUVAGE (sebsauvage.net)
 * @license   https://www.opensource.org/licenses/zlib-license.php The zlib/libpng License
 *
 * @version   1.6.2
 */

namespace PrivateBin;

use Exception;

/**
 * YourlsProxy.
 *
 * Forwards a URL for shortening to YOURLS (your own URL shortener) and stores
 * the result.
 */
class YourlsProxy
{
    /**
     * error message.
     *
     * @var string
     */
    private $_error = '';

    /**
     * shortened URL.
     *
     * @var string
     */
    private $_url = '';

    /**
     * constructor.
     *
     * initializes and runs PrivateBin
     *
     * @param string $link
     */
    public function __construct(Configuration $conf, $link)
    {
        if (false === strpos($link, $conf->getKey('basepath').'?')) {
            $this->_error = 'Trying to shorten a URL that isn\'t pointing at our instance.';

            return;
        }

        $yourls_api_url = $conf->getKey('apiurl', 'yourls');
        if (empty($yourls_api_url)) {
            $this->_error = 'Error calling YOURLS. Probably a configuration issue, like wrong or missing "apiurl" or "signature".';

            return;
        }

        $data = file_get_contents(
            $yourls_api_url,
            false,
            stream_context_create(
                [
                    'http' => [
                        'method' => 'POST',
                        'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                        'content' => http_build_query(
                            [
                                'signature' => $conf->getKey('signature', 'yourls'),
                                'format' => 'json',
                                'action' => 'shorturl',
                                'url' => $link,
                            ]
                        ),
                    ],
                ]
            )
        );

        try {
            $data = Json::decode($data);
        } catch (Exception $e) {
            $this->_error = 'Error calling YOURLS. Probably a configuration issue, like wrong or missing "apiurl" or "signature".';
            error_log('Error calling YOURLS: '.$e->getMessage());

            return;
        }

        if (
            !is_null($data)
            && array_key_exists('statusCode', $data)
            && 200 == $data['statusCode']
            && array_key_exists('shorturl', $data)
        ) {
            $this->_url = $data['shorturl'];
        } else {
            $this->_error = 'Error parsing YOURLS response.';
        }
    }

    /**
     * Returns the (untranslated) error message.
     *
     * @return string
     */
    public function getError()
    {
        return $this->_error;
    }

    /**
     * Returns the shortened URL.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->_url;
    }

    /**
     * Returns true if any error has occurred.
     *
     * @return bool
     */
    public function isError()
    {
        return !empty($this->_error);
    }
}
