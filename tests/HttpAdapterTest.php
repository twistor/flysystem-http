<?php

namespace Twistor\Flysystem\Http;

use League\Flysystem\AdapterInterface;
use League\Flysystem\Config;

/**
 * @covers \Twistor\Flysystem\Http\HttpAdapter
 */
class HttpAdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * The HTTP adapter.
     *
     * @var \Twistor\Flysystem\Http\HttpAdapter
     */
    protected $adapter;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->adapter = new HttpAdapter('http://example.com');
    }

    public function testCopy()
    {
        $this->assertFalse($this->adapter->copy('file.txt', 'other.txt'));
    }

    public function testCreateDir()
    {
        $this->assertFalse($this->adapter->createDir('file.txt/dir', new Config()));
    }

    public function testDelete()
    {
        $this->assertFalse($this->adapter->delete('file.txt'));
    }

    public function testDeleteDir()
    {
        $this->assertFalse($this->adapter->deleteDir('dir'));
    }

    public function testGetBase()
    {
        $this->assertSame('http://example.com', $this->adapter->getBase());
    }

    public function testGetMetadata()
    {
        $result = $this->adapter->getMetadata('foo/pass');

        $this->assertSame('file', $result['type']);
        $this->assertSame('foo/pass', $result['path']);
        $this->assertSame(AdapterInterface::VISIBILITY_PUBLIC, $result['visibility']);
        $this->assertSame('text/html', $result['mimetype']);
        $this->assertSame(42, $result['size']);
        $this->assertSame(1445412480, $result['timestamp']);

        $result = $this->adapter->getMetadata('foo/nomime');

        $this->assertSame('file', $result['type']);
        $this->assertSame('foo/nomime', $result['path']);
        $this->assertSame(AdapterInterface::VISIBILITY_PUBLIC, $result['visibility']);
        $this->assertSame('text/plain', $result['mimetype']);
        $this->assertSame(42, $result['size']);
        $this->assertSame(1445412480, $result['timestamp']);

        $this->assertFalse($this->adapter->getMetadata('foo/404'));

        $this->assertFalse($this->adapter->getMetadata('foo'));
    }

    public function testGetMimetype()
    {
        $result = $this->adapter->getMimetype('foo/pass');

        $this->assertSame('text/html', $result['mimetype']);
    }

    public function testGetSize()
    {
        $result = $this->adapter->getSize('foo/pass');

        $this->assertSame(42, $result['size']);
    }

    public function testGetTimestamp()
    {
        $result = $this->adapter->getTimestamp('foo/pass');

        $this->assertSame(1445412480, $result['timestamp']);
    }

    public function testGetVisibility()
    {
        $result = $this->adapter->getVisibility('foo');
        $this->assertSame(AdapterInterface::VISIBILITY_PUBLIC, $result['visibility']);

        $adapter = new HttpAdapter('http://user:pass@example.com');
        $result = $adapter->getVisibility('foo');
        $this->assertSame(AdapterInterface::VISIBILITY_PRIVATE, $result['visibility']);

        $adapter = new HttpAdapter('http://user@example.com');
        $result = $adapter->getVisibility('foo');
        $this->assertSame(AdapterInterface::VISIBILITY_PRIVATE, $result['visibility']);
    }

    public function testHas()
    {
        $this->assertTrue($this->adapter->has('foo/pass'));
        $this->assertFalse($this->adapter->has('foo'));
    }

    public function testListContents()
    {
        $this->assertSame([], $this->adapter->listContents('dir'));
    }

    public function testRead()
    {
        $result = $this->adapter->read('foo/pass');
        $this->assertSame('response text', $result['contents']);
        $this->assertSame('foo/pass', $result['path']);

        $result = $this->adapter->read('foo/bar');
        $this->assertSame(false, $result);
    }

    public function testReadStream()
    {
        $result = $this->adapter->readStream('foo/pass');
        $this->assertSame('response text', stream_get_contents($result['stream']));
        $this->assertSame('foo/pass', $result['path']);

        $result = $this->adapter->readStream('foo/bar');
        $this->assertSame(false, $result);
    }

    public function testRename()
    {
        $this->assertFalse($this->adapter->rename('file.txt', 'new_file.txt'));
    }

    public function testSetContext()
    {
        $this->adapter->setContext(['foo' => ['bar' => 'baz']]);
    }

    public function testSetVisibility()
    {
        $this->setExpectedException(('\LogicException'));

        $this->adapter->setVisibility('foo', AdapterInterface::VISIBILITY_PUBLIC);
    }

    public function testUpdate()
    {
        $this->assertFalse($this->adapter->update('file.txt', 'contents', new Config()));
    }

    public function testUpdateStream()
    {
        $this->assertFalse($this->adapter->updateStream('file.txt', 'contents', new Config()));
    }

    public function testWrite()
    {
        $this->assertFalse($this->adapter->write('file.txt', 'contents', new Config()));
    }

    public function testWriteStream()
    {
        $this->assertFalse($this->adapter->writeStream('file.txt', 'contents', new Config()));
    }
}

function file_get_contents($path)
{
    if (strpos($path, 'pass') !== false) {
        return 'response text';
    }

    return false;
}

function fopen($path, $mode)
{
    if (strpos($path, 'pass') !== false) {
        return \fopen('data://text/plain,response text', $mode);
    }

    return false;
}

function get_headers($path)
{
    if (strpos($path, 'pass') !== false) {
        return [
            0 => 'HTTP/1.0 200 OK',
            'Content-Type' => 'text/html; charset=utf-8',
            'Content-Length' => '42',
            'Last-Modified' => 'Wed, 21 Oct 2015 07:28:00 GMT',
        ];
    }

    if (strpos($path, 'nomime') !== false) {
        return [
            0 => 'HTTP/1.0 200 OK',
            'Content-Length' => '42',
            'Last-Modified' => 'Wed, 21 Oct 2015 07:28:00 GMT',
        ];
    }

    if (strpos($path, '404') !== false) {
        return [0 => 'HTTP/1.0 404 OK'];
    }

    return false;
}
