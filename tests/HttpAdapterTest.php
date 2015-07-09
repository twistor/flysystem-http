<?php

use League\Flysystem\Config;
use League\Flysystem\Http\HttpAdapter;

/**
 * @coversDefaultClass \League\Flysystem\Http\HttpAdapter
 */
class HttpAdapterTest  extends \PHPUnit_Framework_TestCase
{
    /**
     * The HTTP adapter.
     *
     * @var \League\Flysystem\Http\HttpAdapter
     */
    protected $adapter;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->adapter = new HttpAdapter('http://example.com');
    }

    /**
     * @covers ::copy
     */
    public function testCopy()
    {
        $this->assertFalse($this->adapter->copy('file.txt', 'other.txt'));
    }

    /**
     * @covers ::createDir
     */
    public function testCreateDir()
    {
        $this->assertFalse($this->adapter->createDir('file.txt/dir', new Config()));
    }

    /**
     * @covers ::delete
     */
    public function testDelete()
    {
        $this->assertFalse($this->adapter->delete('file.txt'));
    }

    /**
     * @covers ::deleteDir
     */
    public function testDeleteDir()
    {
        $this->assertFalse($this->adapter->deleteDir('dir'));
    }

    /**
     * @covers ::getMetaData
     */
    public function testGetMetadata()
    {
    }

    /**
     * @covers ::getMimetype
     */
    public function testGetMimetype()
    {
    }

    /**
     * @covers ::getSize
     */
    public function testGetSize()
    {
    }

    /**
     * @covers ::getTimestamp
     */
    public function testGetTimestamp()
    {
    }

    /**
     * @covers ::getVisibility
     */
    public function testGetVisibility()
    {
    }

    /**
     * @covers ::has
     */
    public function testHas()
    {
    }

    /**
     * @covers ::listContents
     */
    public function testListContents()
    {
        $this->assertSame([], $this->adapter->listContents('dir'));
    }

    /**
     * @covers ::read
     */
    public function testRead()
    {
    }

    /**
     * @covers ::readStream
     */
    public function testReadStream()
    {
    }

    /**
     * @covers ::rename
     */
    public function testRename()
    {
        $this->assertFalse($this->adapter->rename('file.txt', 'new_file.txt'));
    }

    /**
     * @covers ::setVisibility
     */
    public function testSetVisibility()
    {
    }

    /**
     * @covers ::update
     */
    public function testUpdate()
    {
        $this->assertFalse($this->adapter->update('file.txt', 'contents', new Config()));
    }

    /**
     * @covers ::write
     */
    public function testWrite()
    {
        $this->assertFalse($this->adapter->write('file.txt', 'contents', new Config()));
    }
}
