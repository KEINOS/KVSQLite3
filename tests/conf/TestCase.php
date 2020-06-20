<?php

namespace KEINOS\Tests;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @var array
     */
    public array $list_path_file_temp = [];

    public const PREFIX_FILE_TEMP = 'UnitTest-';

    public function dataProviderForFilePath()
    {
        return [
            'in_memory' => [null],
            'in_temp'   => [''],
            'in_file'   => [$this->getPathFileTemp()],
        ];
    }

    public function getPathFileTemp(): string
    {
        // Create random and temporary file name and store.
        $path_file_temp = tempnam(sys_get_temp_dir(), self::PREFIX_FILE_TEMP);
        $this->list_path_file_temp[] = $path_file_temp;

        return $path_file_temp;
    }

    public function setUp(): void
    {
        if (! is_writable(sys_get_temp_dir())) {
            $this->fail('Temporary directory is not writable: ' . sys_get_temp_dir());
        }
    }

    public function tearDown(): void
    {
        // Clean up all the temp files
        foreach ($this->list_path_file_temp as $path_file_temp) {
            if (file_exists($path_file_temp)) {
                unlink($path_file_temp);
            }
        }
    }
}
