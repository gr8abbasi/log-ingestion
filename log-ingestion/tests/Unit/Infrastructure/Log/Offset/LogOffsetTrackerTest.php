<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Log\Offset;

use Infrastructure\Log\Offset\LogOffsetTracker;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;

class LogOffsetTrackerTest extends TestCase
{
    private string $logFilePath = '/path/to/log/file';
    private vfsStreamDirectory $root;

    protected function setUp(): void
    {
        parent::setUp();

        $this->root = vfsStream::setup('root');
    }

    public function testConstructorReadsOffsetFromFile(): void
    {

        $offsetContent = "123";

        vfsStream::newDirectory('path/to/log', 777)->at($this->root);
        vfsStream::newFile('path/to/log/file.offset')
        ->withContent($offsetContent)
            ->at($this->root);

        $tracker = new LogOffsetTracker(vfsStream::url('root').$this->logFilePath);

        $this->assertEquals(123, $tracker->getLastOffset());
    }

    public function testUpdateOffsetWritesToFile(): void
    {
        vfsStream::newDirectory('path/to/log')->at($this->root);

        $tracker = new LogOffsetTracker(vfsStream::url('root').$this->logFilePath);

        $tracker->updateOffset(456);

        $updatedFileContent = file_get_contents(vfsStream::url('root/path/to/log/file.offset'));
        $this->assertEquals('456', $updatedFileContent);
    }
}
