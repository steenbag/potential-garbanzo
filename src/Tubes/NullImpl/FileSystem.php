<?php namespace Steenbag\Tubes\NullImpl;

class FileSystem implements \Steenbag\Tubes\Contract\FileSystem
{

    /**
     * Write content to a file.
     *
     * @param $path
     * @param $content
     * @return int
     * @throws \Exception
     */
    public function put($path, $content)
    {
        if(is_writable($path) === false || !$handle = fopen($path, 'w')) {
            throw new \Exception("Unable to open file for writing.");
        }
        if (fwrite($handle, $content) === false) {
            throw new \Exception("Unable to write to file.");
        }
        fclose($handle);
    }

    /**
     * Return true if a file exists.
     *
     * @param $paths
     * @return bool
     */
    public function exists($paths)
    {
        if (is_array($paths)) {
            return array_map([$this, 'exists'], $paths);
        }
        return file_exists($paths);
    }

    /**
     * Delete the given file.
     *
     * @param $paths
     * @return bool
     */
    public function delete($paths)
    {
        if (is_array($paths)) {
            return array_map([$this, 'delete'], $paths);
        }

        return unlink($paths);
    }
}
