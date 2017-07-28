<?php

namespace WhyooOs\Util;


# see http://docs.python.org/2/library/os.path.html for inspiration
class UtilFilesystem
{


    public static function getNextFreeFilename($pathFile, $idxStart = 1)
    {
        if (!file_exists($pathFile)) {
            return $pathFile;
        }

        $ext = self::getExtension($pathFile);
        $base = self::getWithoutExtension($pathFile);
        for ($idx = $idxStart; $idx < 999999999; $idx++) {
            $newFilePath = $base . '-' . $idx . '.' . $ext;
            ##Util::dd($newFilePath);
            if (!file_exists($newFilePath)) {
                return $newFilePath;
            }
        }
    }


    /**
     * returns LOWERCASE extension
     *
     * @param $filePath
     * @return string eg "png"
     */
    public static function getExtension($filePath)
    {
        $pos = strrpos($filePath, '.');
        if ($pos !== false) {
            return strtolower(substr($filePath, $pos + 1));
        } else { // no extension
            return '';
        }
    }

    /**
     * alias
     *
     * @param $filename
     * @return bool|string
     */
    public static function stripExtension($filename)
    {
        return self::getWithoutExtension($filename);
    }


    /**
     * @param $filePath
     * @return string file path without extension eg "/tmp/somefile"
     */
    public static function getWithoutExtension($filePath)
    {
        $pos = strrpos($filePath, '.');
        if ($pos !== false) {
            return substr($filePath, 0, $pos);
        } else { // no extension
            return $filePath;
        }
    }


    /**
     * not recursive .. returns files and directories
     */
    public static function scanDir($path)
    {
        $ret = [];
        if ($handle = opendir($path)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    $ret[] = $entry;
                }
            }
            closedir($handle);
        }

        asort($ret);

        return $ret;
    }


    /**
     * recursive .. returns files only
     */
    public static function scanDirForFilesRecursive($path, $prefix = '')
    {
        $ret = [];
        if ($handle = opendir($path)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    $fullPath = self::joinPaths($path, $entry);
                    if (is_dir($fullPath)) {
                        $ret = array_merge($ret, self::scanDirForFilesRecursive($fullPath, self::joinPaths($prefix, $entry)));
                    } else {
                        $ret[] = self::joinPaths($prefix, $entry);
                    }
                }
            }
            closedir($handle);
        }
        //dump($ret);
        return $ret;
    }




//    # similar to python's os.walk
//    // IS BUGGY/NOT WORKING CORRECTLY
//    public function findFilesRecursive($dir = '.', $pattern = '~.*~')
//    {
//        $ret = array();
//        $prefix = $dir . '/';
//        $dir = @dir($dir);
//        if (!is_object($dir)) {
//            return [];
//        }
//        while (false !== ($file = $dir->read())) {
//            if ($file === '.' || $file === '..') {
//                continue;
//            }
//            $file = $prefix . $file;
//            if (is_dir($file)) {
//                $ret = array_merge($ret, self::findFilesRecursive($file, $pattern));
//            }
//            if (preg_match($pattern, $file)) {
//                #echo $file . "\n";
//                $ret[] = $file;
//            }
//        }
//        return $ret;
//    }


    public static function deleteDirectoryRecursive($path)
    {
        try {
            $it = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($it as $file) {
                if (in_array($file->getBasename(), array('.', '..'))) {
                    continue;
                } elseif ($file->isDir()) {
                    rmdir($file->getPathname());
                } elseif ($file->isFile() || $file->isLink()) {
                    unlink($file->getPathname());
                }
            }
        } catch (\Exception $e) {
            // ...
        }
        @rmdir($path);
    }


    /**
     * returns alphabetically sorted filtered list of files
     *
     * @param $dir
     * @param $bRecursive
     * @return array
     */
    public static function findImages($dir, $bRecursive = true)
    {
        $extensionsLowerCase = ['jpg', 'jpeg', 'png', 'gif'];

        if ($bRecursive) {
            $files = self::scanDirForFilesRecursive($dir);
        } else {
            $files = self::scanDir($dir);
        }
        $ret = array_filter($files, function ($filename) use ($extensionsLowerCase) {
            //echo "#".self::getExtension( $filename);
            return in_array(self::getExtension($filename), $extensionsLowerCase);
        });

        asort($ret);

        return $ret;
    }


    public static function joinPaths()
    {
        $paths = array();

        foreach (func_get_args() as $arg) {
            if ($arg !== '') {
                $paths[] = $arg;
            }
        }

        return preg_replace('#/+#', '/', join('/', $paths));
    }


    /**
     * not recursive
     *
     * @param string $path
     * @return array directory names relative to $path (sorted by name)
     */
    public static function findDirectories($path)
    {
        $files = self::scanDir($path);
        $dirs = array_filter($files, function ($filename) use ($path) {
            return is_dir(self::joinPaths($path, $filename));
        });
        asort($dirs);

        return array_values($dirs);
    }


    /**
     * @param $path
     */
    public static function mkdirIfNotExists($path)
    {
        if (!is_dir($path)) {
            self::mkdir($path);
        }
    }

    /**
     * creates directory recursively with the right permissions
     * @param string $path
     * @param string $perm
     * @throws \Exception
     */
    public static function mkdir($path, $perm = 0777 /*, $maxLevelsDown=3*/)
    {
        $parts = explode('/', $path);
//		$offset = count($parts) - $maxLevelsDown;
//		$parts = array_slice( $parts, $offset, $maxLevelsDown);

        $p = '';
        foreach ($parts as $part) {
            $p = $p . '/' . $part;
            if (!is_dir($p)) {
                if (!@mkdir($p, $perm)) {
//					return false;
                    throw new \Exception("could not create directory $p");
                }
                @chmod($p, $perm);
            }
            if (!@chdir($p)) {
//				return false
                throw new \Exception("could not enter $p");
            }
        }
    }


    /**
     * move content of one directory to another
     *
     * @param $pathOld
     * @param $pathNew
     */
    public static function moveFiles($pathOld, $pathNew)
    {
        foreach (scandir($pathOld) as $fname) {
            if ($fname != '.' && $fname != '..') {
                rename(self::joinPaths($pathOld, $fname), self::joinPaths($pathNew, $fname));
            }
        }
    }




//    # similar to python's os.walk
//    // IS BUGGY/NOT WORKING CORRECTLY
//    public function findFilesRecursive($dir = '.', $pattern = '~.*~')
//    {
//        $ret = array();
//        $prefix = $dir . '/';
//        $dir = @dir($dir);
//        if (!is_object($dir)) {
//            return [];
//        }
//        while (false !== ($file = $dir->read())) {
//            if ($file === '.' || $file === '..') {
//                continue;
//            }
//            $file = $prefix . $file;
//            if (is_dir($file)) {
//                $ret = array_merge($ret, self::findFilesRecursive($file, $pattern));
//            }
//            if (preg_match($pattern, $file)) {
//                #echo $file . "\n";
//                $ret[] = $file;
//            }
//        }
//        return $ret;
//    }


    /**
     * not recursive .. returns directories (alphabetically sorted)
     */
    public static function getDirs($path)
    {
        $dirs = [];
        if ($handle = opendir($path)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != ".." && is_dir(self::joinPaths($path, $entry))) {
                    $dirs[] = realpath(self::joinPaths($path, $entry));
                }
            }
            closedir($handle);
        }
        sort($dirs);

        return $dirs;
    }


    /**
     * returns full pathes to all files (no directories, recursive)
     */
    public static function getFiles($path)
    {
        $rii = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
        $files = [];
        foreach ($rii as $file) {
            if ($file->isDir()) {
                continue;
            }
            $files[] = $file->getPathname();
        }

        return $files;
    }


}
