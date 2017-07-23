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




    public static function stripExtension($filename)
    {
        $pos = strrpos($filename, '.');
        if ($pos !== false) {
            return substr($filename, 0, $pos);
        } else { // no extension
            return $filename;
        }
    }




    public static function getExtension($filename)
    {
        $pos = strrpos($filename, '.');
        if ($pos !== false) {
            return strtolower(substr($filename, $pos + 1));
        } else { // no extension
            return '';
        }
    }

    public static function getWithoutExtension($filename)
    {
        $pos = strrpos($filename, '.');
        if ($pos !== false) {
            return strtolower(substr($filename, 0, $pos));
        } else { // no extension
            return '';
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
        if (!file_exists($path) && !is_dir($path)) {
            mkdir($path);
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

}
