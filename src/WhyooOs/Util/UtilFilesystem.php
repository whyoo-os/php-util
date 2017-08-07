<?php

namespace WhyooOs\Util;



# see http://docs.python.org/2/library/os.path.html for inspiration
# will not work under windows .. it assumes slash (/) as separator
class UtilFilesystem
{


    /**
     * @param $pathFile
     * @param int $idxStart
     * @return string
     */
    public static function getNextFreeFilename($pathFile, $idxStart = 1)
    {
        if (!file_exists($pathFile)) {
            return $pathFile;
        }

        $ext = self::getExtension($pathFile);
        $base = self::getWithoutExtension($pathFile);
        for ($idx = $idxStart; $idx < 999999999; $idx++) {
            $newFilePath = $base . '-' . $idx . '.' . $ext;
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


    /**
     * 08/2017
     * from: https://stackoverflow.com/a/2021729/2848530
     *
     * @param $filename
     * @return false|string
     */
    public static function sanitizeFilename($filename)
    {
        // Remove anything which isn't a word, whitespace, number
        // or any of the following caracters -_~,;[]().
        // If you don't need to handle multi-byte characters
        // you can use preg_replace rather than mb_ereg_replace
        // Thanks @Łukasz Rysiak!
        $filename = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $filename);
        // Remove any runs of periods (thanks falstro!)
        $filename = mb_ereg_replace("([\.]{2,})", '', $filename);

        return $filename;
    }



    // from smartdonation
    // buggy? not working correctly?
    # similar to python's os.walk
    function findFilesRecursive($dir = '.', $pattern = '~.*~')
    {
        $ret = [];
        $prefix = $dir . '/';
        $dir = dir($dir);
        while (false !== ($file = $dir->read())) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $file = $prefix . $file;
            if (is_dir($file)) {
                $ret = array_merge($ret, self::findFilesRecursive($file, $pattern));
            }
            if (preg_match($pattern, $file)) {
                #echo $file . "\n";
                $ret[] = $file;
            }
        }
        return $ret;
    }


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


    /**
     * former rawDataToPhysicalFile
     *
     * moved from UtilImage to here
     * decode image and save to temporary file
     *
     * @param $base64EncodedString
     * @param $pathDestDir
     * @return string
     * @throws \Exception
     */
    public static function base64ToPhysicalFile($base64EncodedString, string $pathDestDir)
    {
        $binData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64EncodedString));

        // check mime type .. must be an image
        $finfo = new \finfo(FILEINFO_MIME);
        $mime = $finfo->buffer($binData);
        if (!preg_match('#image/(\w+)#', $mime, $gr)) {
            // not an image
            throw new \Exception("not an image: $mime");
        }

        $imageType = $gr[1];

        UtilAssert::assertInArray($imageType, ['png', 'jpeg', 'gif'], "unsupported image type: $imageType");

        // how image will be named
        $destFilename = sprintf('%s-%s.%s', md5(microtime()), md5(rand() . 'xxx' . rand()), $imageType);
        $pathDest = UtilFilesystem::joinPaths($pathDestDir, $destFilename);
        file_put_contents($pathDest, $binData);

        return $pathDest;
    }


    /**
     * @param string $pathDirectory
     * @param string $filename
     * @return string $newFilename the new filename test.jpg, test-1.jpg, test-2.jpg etc
     */
    public static function getUniqueFilename(string $pathDirectory, string $filename)
    {
        $newFilename = $filename;

        $path_parts = pathinfo($filename);

        $counter = 1;
        while (file_exists("$pathDirectory/$newFilename")) {
            $newFilename = $path_parts['filename'] . "-$counter." . $path_parts['extension'];
            $counter++;
        }
        return $newFilename;
    }


    /**
     * eg: removeLeadingDirectories('/tmp/aaa/bbb/ccc/ddd/eee', 2) ==> 'ddd/eee'
     *
     * @param string $path
     * @param int $numBack
     * @return string
     */
    public static function removeLeadingDirectories(string $path, int $numBack = 1, string $directorySeparator='/') : string
    {
        $tmp = explode($directorySeparator, $path);

        return implode($directorySeparator, array_slice($tmp, count($tmp) - $numBack, $numBack));
    }



}
