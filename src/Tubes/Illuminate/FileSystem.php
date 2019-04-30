<?php namespace Steenbag\Tubes\Illuminate;

class FileSystem implements \Steenbag\Tubes\Contract\FileSystem
{

    protected $illuminateFilesystem;

    public function __construct(\Illuminate\Filesystem\Filesystem $filesystem)
    {
        $this->illuminateFilesystem = $filesystem;
    }

    /**
     * Write content to a file.
     *
     * @param $path
     * @param $content
     * @return int
     */
    public function put($path, $content)
    {
        return $this->illuminateFilesystem->put($path, $content);
    }

    /**
     * Return true if a file exists.
     *
     * @param $paths
     * @return bool
     */
    public function exists($paths)
    {
        return $this->illuminateFilesystem->exists($paths);
    }

    /**
     * Delete the given file.
     *
     * @param $paths
     * @return bool
     */
    public function delete($paths)
    {
        return $this->illuminateFilesystem->delete($paths);
    }
}
