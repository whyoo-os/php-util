<?php


namespace WhyooOs\Util;


/**
 * image utility class
 */
class UtilImage
{


    const RESIZE_MODE_STRETCH = 'STRETCH';
    const RESIZE_MODE_INSET = 'INSET'; // SHRINK  / NO_CROP / FIT / Resize to fit inside box
    const RESIZE_MODE_CROP = 'CROP'; // FILL_CROP / OUTBOUND

    private static $defaultJpegQuality = 95;


    // -----------------------------------------------------------------------------------------------------------
    // -----------------------------------------------------------------------------------------------------------
    // -----------------------------------------------------------------------------------------------------------
    // -----------------------------------------------------------------------------------------------------------
    // -----------------------------------------------------------------------------------------------------------


    /**
     * wrapper for php's imagecreatefrom***() functions
     *
     * @param $pathImg
     * @return resource
     * @throws \Exception
     */
    public static function loadImage($pathImg)
    {
        $extension = UtilFilesystem::getExtension($pathImg);
        if ($extension == 'jpg' || $extension == 'jpeg') {
            return imagecreatefromjpeg($pathImg);
        } elseif ($extension == 'png') {
            return imagecreatefrompng($pathImg);
        } elseif ($extension == "gif") {
            return imagecreatefromgif($pathImg);
        }

        throw new \Exception("unknown extension $extension for file $pathImg");
    }


    /**
     * wrapper for php's image***() functions
     *
     * @param $image
     * @param $pathDest
     * @param null $jpegQuality default is self::$defaultJpegQuality
     * @return bool
     * @throws \Exception
     */
    public static function saveImage($image, $pathDest, $jpegQuality = null)
    {
        $extension = UtilFilesystem::getExtension($pathDest);
        if ($extension == 'jpg' || $extension == 'jpeg') {
            if (empty($jpegQuality)) {
                $jpegQuality = self::$defaultJpegQuality;
            }
            return imagejpeg($image, $pathDest, $jpegQuality);
        } else if ($extension == 'png') {
            return imagepng($image, $pathDest);
        } elseif ($extension == "gif") {
            return imagegif($image, $pathDest);
        } else {
            throw new \Exception("unknown extension $extension for file $pathDest");
        }

    }


    /**
     * adds (white) stripes at L+R or T+B of image to make it square
     *
     * used for marketer fixtures to have nice square white logos
     *
     * @param $pathSrc
     * @param $pathDest
     * @param $backgroundColor
     */
    public static function extendToSquare(string $pathSrc, string $pathDest, int $backgroundColor = 0xffffff)
    {
        list($x, $y) = getimagesize($pathSrc);

        // ---- if image is already square --> just copy (if pathSrc and pathDest are different)
        if ($x == $y) {
            if (realpath($pathSrc) != realpath($pathDest)) {
                copy($pathSrc, $pathDest);
            }
            return;
        }

        $endSize = max($x, $y);

        if ($x < $endSize) {
            $offsetX = ($endSize - $x) / 2;
            $offsetY = 0;
        } elseif ($y < $endSize) {
            $offsetX = 0;
            $offsetY = ($endSize - $y) / 2;
        } else {
            // it's already a square
            $offsetX = 0;
            $offsetY = 0;
        }


        $imgSrc = self::loadImage($pathSrc);
        $imgDest = imagecreatetruecolor($endSize, $endSize);
        imagefill($imgDest, 0, 0, $backgroundColor);
        imagecopyresampled($imgDest, $imgSrc, $offsetX, $offsetY, 0, 0, $x, $y, $x, $y);

        self::saveImage($imgDest, $pathDest);
    }


    // ----------------------------------------------------------------------------------------

    /**
     * unused
     *
     * @param $pathImage
     * @return string
     */
    public static function getAspectRatio($pathImage)
    {
        list($w, $h) = getimagesize($pathImage);
        $min = min($w, $h);
        for ($teiler = $min; $teiler > 0; $teiler--) {
            if (($w % $teiler == 0) && ($h % $teiler == 0)) {
                return sprintf("%d:%d", $w / $teiler, $h / $teiler);
            }
        }
        return sprintf("%d:%d", $w, $h);
    }


    public static function rotateImage($pathSrc, $pathDest, $degrees)
    {
        // Image 2 .... is Image 90 degrees rotated
        $imgTmp = new \Imagick($pathSrc);
        $imgTmp->rotateImage(new \ImagickPixel('none'), $degrees);
        $imgTmp->trimImage(0);
        $imgTmp->writeImage($pathDest);
        $imgTmp->destroy();

        return $pathDest;
    }


    /**
     * @param string $fullPathImage
     * @param string $pathCropped
     * @param int $x1
     * @param int $y1
     * @param int $x2
     * @param int $y2
     */
    public static function cropImage(string $fullPathImage, string $pathCropped, $x1, $y1, $x2, $y2)
    {
        $im = UtilImage::loadImage($fullPathImage);
        $im2 = imagecrop($im, [
            'x' => $x1,
            'y' => $y1,
            'width' => $x2 - $x1,
            'height' => $y2 - $y1
        ]);

        UtilImage::saveImage($im2, $pathCropped);
    }


    /**
     * @param string $fullPathImage
     * @return array
     */
    public static function getImageSize(string $fullPathImage)
    {
        list($w, $h) = getimagesize($fullPathImage);

        return [
            'w' => $w,
            'h' => $h
        ];
    }

    /**
     * wrapper function around
     * used by ebayGen
     *
     * @param $pathSrc
     * @param $pathDest
     * @param $pathTag
     * @param $position
     * @param float $scalePercent size of the watermark
     */
    public static function watermarkImage($pathSrc, $pathDest, $pathTag, $position, $scalePercent)
    {
        $imageTagger = new \WhyooOs\HelperClasses\ImageTagger();
        $imageTagger->tagImage($pathSrc, $pathTag, $position, $scalePercent, $pathDest); // TODO: fix the hardcoded size=60%
    }


    /**
     * for embedding image in html .. useful when using dompdf
     *
     * 07/2017
     *
     * @param $pathImage
     * @return string
     */
    public static function base64EncodePhysicalImage($pathImage)
    {
        return 'data:' . mime_content_type($pathImage) . ';base64,' . base64_encode(file_get_contents($pathImage));
    }


    /**
     * private helper
     *
     * @param ImageResize $image
     * @param array $dimensions
     * @param string $resizeMode
     * @throws \Exception
     */
    private static function _resize(ImageResize $image, int $width, int $height, string $resizeMode)
    {
        if ($resizeMode == self::RESIZE_MODE_STRETCH) {
            $image->resize($width, $height);
        } elseif ($resizeMode == self::RESIZE_MODE_INSET) {
            $image->resizeToBestFit($width, $height);
        } elseif ($resizeMode == self::RESIZE_MODE_CROP) {
            $image->crop($width, $height);
        } else {
            throw new \Exception('Unknown resizeMode: ' . $resizeMode);
        }
    }


    /**
     * private helper
     *
     * @param string $size eg "300x400" or "300"
     * @return array with 2 elements: with and height
     */
    private static function _sizeStringToIntArray($size)
    {
        $ret = explode('x', $size);
        if (count($ret) == 1) {
            $ret[1] = $ret[0];
        }
        UtilAssert::assertArrayLength($ret, 2);

        return array_map('intval', $ret);
    }


    /**
     * 07/2017 used by schlegel for stretching pdf-background to cover whole page
     * 08/2017 used by ebaygen
     * 03/2018 does NOT WORK for animated gifs
     * 
     * resizes image to $dimension .. doesn't take care of aspect ratio - image is "stretched"
     * uses eventviva/php-image-resize (composer require eventviva/php-image-resize)
     *
     * @param $pathSrc
     * @param $pathDest
     * @param string $size eg "300x400" or "300"
     * @param string $resizeMode
     * @throws \Exception
     */
    public static function resizeImage(string $pathSrc, string $pathDest, $size, string $resizeMode = self::RESIZE_MODE_STRETCH)
    {
        list($width, $height) = self::_sizeStringToIntArray($size);
        $image = @(new \Eventviva\ImageResize($pathSrc)); // WE SUPPRESS ERRORS HERE because we had problem with illegal exif data
        self::_resize($image, $width, $height, $resizeMode);
        $image->save($pathDest);
    }


    /**
     * 08/2017 used for gridfs files (marketer)
     *
     * @param $bytesSrc
     * @param $pathDest
     * @param string $size eg "300x400" or "300"
     * @param string $resizeMode
     * @throws \Exception
     */
    public static function resizeImageFromBytes($bytesSrc, string $pathDest, $size, string $resizeMode = self::RESIZE_MODE_STRETCH)
    {
        list($width, $height) = self::_sizeStringToIntArray($size);
        $image = @(\Eventviva\ImageResize::createFromString($bytesSrc)); // WE SUPPRESS ERRORS HERE because we had problem with illegal exif data
        self::_resize($image, $width, $height, $resizeMode);
        $image->save($pathDest);
    }


    /**
     * 03/2018 used for gridfs files (marketer) ... for animated emojis / stickers
     * uses coldume/imagecraft FIXME: it flickers .. need better solution https://stackoverflow.com/questions/718491/resize-animated-gif-file-without-destroying-animation
     * @param $bytesSrc
     * @param string $pathDest
     * @param string $size eg "300x400" or "300"
     * @param string $resizeMode
     * @throws \Exception
     */
    public static function resizeGifFromBytes($bytesSrc, string $pathDest, $size, string $resizeMode__CURRENTLY_IGNORED = self::RESIZE_MODE_STRETCH)
    {
        list($width, $height)  = self::_sizeStringToIntArray($size);

        $options = [
            'engine' => 'php_gd',
            'gif_animation' => true,
            'output_format' => 'gif',
            'debug' => false,
        ];
        $builder = new \Imagecraft\ImageBuilder($options);
        $image = $builder
            ->addBackgroundLayer()
            ->contents($bytesSrc)
            ->resize($width, $height, 'shrink')
            ->done()
            ->save();
        if ($image->isValid()) {
            file_put_contents($pathDest, $image->getContents());
        } else {
            echo $image->getMessage() . PHP_EOL;
        }
    }


}

