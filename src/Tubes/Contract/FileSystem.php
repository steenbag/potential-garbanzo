<?php namespace Steenbag\Tubes\Contract;

interface FileSystem
{

    /**
     * Write content to a file.
     *
     * @param $path
     * @param $content
     * @return int
     */
    public function put($path, $content);

    /**
     * Return true if a file exists.
     *
     * @param $paths
     * @return bool
     */
    public function exists($paths);

    /**
     * Delete the given file.
     *
     * @param $paths
     * @return bool
     */
    public function delete($paths);

}
