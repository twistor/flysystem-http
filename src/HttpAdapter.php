<?php

namespace Twistor\Flysystem\Http;

use League\Flysystem\AdapterInterface;
use League\Flysystem\Config;
use League\Flysystem\Util\MimeType;

/**
 * Provides an adapter using PHP native HTTP functions.
 */
class HttpAdapter implements AdapterInterface
{
    /**
     * The base URL.
     *
     * @var string
     */
    protected $base;

    /**
     * @var array
     */
    protected $context;

    /**
     * @var bool
     */
    protected $supportsHead;

    /**
     * The visibility of this adapter.
     *
     * @var string
     */
    protected $visibility = AdapterInterface::VISIBILITY_PUBLIC;

    /**
     * Constructs an HttpAdapter object.
     *
     * @param string $base         The base URL
     * @param bool   $supportsHead Whether the endpoint supports HEAD requests
     * @param array  $context      Context options
     */
    public function __construct($base, $supportsHead = true, array $context = [])
    {
        $this->base = $base;
        $this->supportsHead = $supportsHead;
        $this->context = $context;

        // Add in some safe defaults for SSL/TLS. Don't know why PHPUnit/Xdebug
        // messes this up.
        // @codeCoverageIgnoreStart
        $this->context += [
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
                'SNI_enabled' => true,
                'disable_compression' => true,
            ],
        ];
        // @codeCoverageIgnoreEnd

        if (isset(parse_url($base)['user'])) {
            $this->visibility = AdapterInterface::VISIBILITY_PRIVATE;
        }
    }

    /**
     * @inheritdoc
     */
    public function copy($path, $newpath)
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function createDir($path, Config $config)
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function delete($path)
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function deleteDir($path)
    {
        return false;
    }

    /**
     * Returns the base path.
     *
     * @return string The base path
     */
    public function getBase()
    {
        return $this->base;
    }

    /**
     * @inheritdoc
     */
    public function getMetadata($path)
    {
        if (false === $headers = $this->head($path)) {
            return false;
        }

        return ['type' => 'file'] + $this->parseMetadata($path, $headers);
    }

    /**
     * @inheritdoc
     */
    public function getMimetype($path)
    {
        return $this->getMetadata($path);
    }

    /**
     * @inheritdoc
     */
    public function getSize($path)
    {
        return $this->getMetadata($path);
    }

    /**
     * @inheritdoc
     */
    public function getTimestamp($path)
    {
        return $this->getMetadata($path);
    }

    /**
     * @inheritdoc
     */
    public function getVisibility($path)
    {
        return [
            'path' => $path,
            'visibility' => $this->visibility,
        ];
    }

    /**
     * @inheritdoc
     */
    public function has($path)
    {
        return (bool) $this->head($path);
    }

    /**
     * @inheritdoc
     */
    public function listContents($directory = '', $recursive = false)
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function read($path)
    {
        $context = stream_context_create($this->context);
        $contents = file_get_contents($this->buildUrl($path), false, $context);

        if ($contents === false) {
            return false;
        }

        return compact('path', 'contents');
    }

    /**
     * @inheritdoc
     */
    public function readStream($path)
    {
        $context = stream_context_create($this->context);
        $stream = fopen($this->buildUrl($path), 'rb', false, $context);

        if ($stream === false) {
            return false;
        }

        return [
            'path' => $path,
            'stream' => $stream,
        ];
    }

    /**
     * @inheritdoc
     */
    public function rename($path, $newpath)
    {
        return false;
    }

    /**
     * Sets the HTTP context options.
     *
     * @param array $context The context options
     */
    public function setContext(array $context)
    {
        $this->context = $context;
    }

    /**
     * @inheritdoc
     */
    public function setVisibility($path, $visibility)
    {
        throw new \LogicException('HttpAdapter does not support visibility. Path: ' . $path . ', visibility: ' . $visibility);
    }

    /**
     * @inheritdoc
     */
    public function update($path, $contents, Config $conf)
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function updateStream($path, $resource, Config $config)
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function write($path, $contents, Config $config)
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function writeStream($path, $resource, Config $config)
    {
        return false;
    }

    /**
     * Returns the URL to perform an HTTP request.
     *
     * @param string $path
     *
     * @return string
     */
    protected function buildUrl($path)
    {
        $path = str_replace('%2F', '/', $path);
        $path = str_replace(' ', '%20', $path);

        return rtrim($this->base, '/') . '/' . $path;
    }

    /**
     * Performs a HEAD request.
     *
     * @param string $path
     *
     * @return array|false
     */
    protected function head($path)
    {
        $defaults = stream_context_get_options(stream_context_get_default());
        $options = $this->context;

        if ($this->supportsHead) {
            $options['http']['method'] = 'HEAD';
        }

        stream_context_set_default($options);

        $headers = get_headers($this->buildUrl($path), 1);

        stream_context_set_default($defaults);

        if ($headers === false || strpos($headers[0], ' 200') === false) {
            return false;
        }

        return array_change_key_case($headers);
    }

    /**
     * Parses the timestamp out of headers.
     *
     * @param array $headers
     *
     * @return int|false
     */
    protected function parseTimestamp(array $headers)
    {
        if (isset($headers['last-modified'])) {
            return strtotime($headers['last-modified']);
        }

        return false;
    }

    /**
     * Parses metadata out of response headers.
     *
     * @param string $path
     * @param array  $headers
     *
     * @return array
     */
    protected function parseMetadata($path, array $headers)
    {
        $metadata = [
            'path' => $path,
            'visibility' => $this->visibility,
            'mimetype' => $this->parseMimeType($path, $headers),
        ];

        if (false !== $timestamp = $this->parseTimestamp($headers)) {
            $metadata['timestamp'] = $timestamp;
        }

        if (isset($headers['content-length']) && is_numeric($headers['content-length'])) {
            $metadata['size'] = (int) $headers['content-length'];
        }

        return $metadata;
    }

    /**
     * Parses the mimetype out of response headers.
     *
     * @param string $path
     * @param array  $headers
     *
     * @return string
     */
    protected function parseMimeType($path, array $headers)
    {
        if (isset($headers['content-type'])) {
            list($mimetype) = explode(';', $headers['content-type'], 2);

            return trim($mimetype);
        }

        // Remove any query strings or fragments.
        list($path) = explode('#', $path, 2);
        list($path) = explode('?', $path, 2);

        return MimeType::detectByFilename($path);
    }
}
