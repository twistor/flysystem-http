<?php

namespace League\Flysystem\Http;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Config;
use League\Flysystem\Util\MimeType;

/**
 * Uses Guzzle as a backend for HTTP URLs.
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
     * The Guzzle HTTP client.
     *
     * @var \GuzzleHttp\ClientInterface
     */
    protected $client;

    /**
     * The visibility of this adapter.
     *
     * @var string
     */
    protected $visibility = AdapterInterface::VISIBILITY_PUBLIC;

    /**
     * Constructs an Http object,
     *
     * @param string $base The base URL.
     * @param \GuzzleHttp\ClientInterface $client An optional Guzzle client.
     */
    public function __construct($base, ClientInterface $client = null)
    {
        $this->client = $client;

        $parsed = parse_url($base);
        $this->base = $parsed['scheme'] . '://';

        if (isset($parsed['user'])) {
            $this->visibility = AdapterInterface::VISIBILITY_PRIVATE;

            $this->base .= $parsed['user'];

            if (isset($parsed['pass']) && $parsed['pass'] !== '') {
                $this->base .= ':' . $parsed['pass'];
            }

            $this->base .= '@';
        };

        $this->base .= $parsed['host'];
    }

    /**
     * {@inheritdoc}
     */
    public function copy($path, $newpath)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function createDir($path, Config $config)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($path)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteDir($path)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($path)
    {
        $response = $this->client->head($this->base . '/' . $path);

        if ($mimetype = $response->getHeader('Content-Type')) {
            $mimetype = trim(explode(';', $mimetype, 2));
        } else {
            // Remove any query strings or fragments.
            list($path) = explode('#', $path, 2);
            list($path) = explode('?', $path, 2);
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            $mimetype = $extension ? MimeType::detectByFileExtension($extension) : 'text/plain';
        }

        return [
            'type' => 'file',
            'path' => $path,
            'timestamp' => (int) strtotime($response->getHeader('Last-Modified')),
            'size' => (int) $response->getHeader('Content-Length'),
            'visibility' => $this->visibility,
            'mimetype' => $mimetype,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getMimetype($path)
    {
        return $this->getMetadata($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getSize($path)
    {
        return $this->getMetadata($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getTimestamp($path)
    {
        return $this->getMetadata($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getVisibility($path)
    {
        return [
            'path' => $path,
            'visibility' => $this->visibility,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function has($path)
    {
        try {
            $response = $this->getClient()->head($this->base . '/' . $path);
        } catch (ClientException $e) {
            return false;
        }

        $code = $response->getStatusCode();

        return $code >= 200 && $code < 300;
    }

    /**
     * {@inheritdoc}
     */
    public function listContents($directory = '', $recursive = false)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function read($path)
    {
        if (! $result = $this->readStream($path)) {
            return false;
        }

        $result['contents'] = stream_get_contents($result['stream']);

        fclose($result['stream']);
        unset($result['stream']);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function readStream($path)
    {
        try {
            $stream = $this->getClient()->get($this->base . '/' . $path)->getBody()->detach();
        }
        catch (ClientException $e) {
            return false;
        }

        return [
            'path' => $path,
            'stream' => $stream,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rename($path, $newpath)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function setVisibility($path, $visibility)
    {
        if ($visibility === $this->visibility) {
            return $this->getVisibility($path);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function update($path, $contents, Config $conf)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function updateStream($path, $resource, Config $config)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function write($path, $contents, Config $config)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function writeStream($path, $resource, Config $config)
    {
        return false;
    }

    /**
     * Returns the Guzzle client.
     *
     * @return \GuzzleHttp\ClientInterface
     */
    protected function getClient()
    {
        if (! isset($this->client)) {
            $this->client = new Client();
        }

        return $this->client;
    }
}
