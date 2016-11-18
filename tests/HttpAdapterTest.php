<?php

use League\Flysystem\AdapterInterface;
use League\Flysystem\Config;
use Twistor\Flysystem\Http\HttpAdapter;

/**
 * @covers \Twistor\Flysystem\Http\HttpAdapter
 */
class HttpAdapterTest  extends \PHPUnit_Framework_TestCase
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

    public function testGetMetadata()
    {
    }

    public function testGetMimetype()
    {
    }

    public function testGetSize()
    {
    }

    public function testGetTimestamp()
    {
    }

    public function testGetVisibility()
    {
    }

    public function testHas()
    {
    }

    public function testListContents()
    {
        $this->assertSame([], $this->adapter->listContents('dir'));
    }

    public function testRead()
    {
    }

    public function testReadStream()
    {
    }

    public function testRename()
    {
        $this->assertFalse($this->adapter->rename('file.txt', 'new_file.txt'));
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

    public function testWrite()
    {
        $this->assertFalse($this->adapter->write('file.txt', 'contents', new Config()));
    }
}
