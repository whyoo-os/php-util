<?php


namespace WhyooOs\Util;

use PHPImageWorkshop\ImageWorkshop;
use WhyooOs\Util\UtilAssert;
use WhyooOs\Util\UtilFilesystem;
use WhyooOs\Util\UtilSymfony;


/**
 * image utility class
 */
class UtilImage
{


/// MARKETER VERSION
/// MARKETER VERSION
/// MARKETER VERSION
/// MARKETER VERSION
/// MARKETER VERSION
/// MARKETER VERSION
    private static $defaultJpegQuality = 95;


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
    public static function saveImage($image, $pathDest, $jpegQuality=null)
    {
        $extension = UtilFilesystem::getExtension($pathDest);
        if ($extension == 'jpg' || $extension == 'jpeg') {
            if( empty($jpegQuality)) {
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
     * adds (white) borders at LR or TB of image to make it square
     *
     * used for fixtures
     * uses some cheap caching using /tmp/
     *
     * @param $pathSrc
     * @param $pathDest
     * @param $backgroundColor
     */
    public static function extendImage($pathSrc, int $backgroundColor = 0xffffff)
    {
        $cacheId = sha1(serialize(func_get_args()));
        $pathDest = '/tmp/' . $cacheId . '.' . UtilFilesystem::getExtension($pathSrc);

        if (file_exists($pathDest)) {
            return $pathDest;
        }

        list($x, $y) = getimagesize($pathSrc);
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

        return $pathDest;
    }



    /**
     * returns web path of icon for a give mime type
     * @param $mimeType
     * @return string
     */
    public static function getMimeTypeIcon($mimeType)
    {
        $pathIconsWeb = "/assets/images/mimetypes/96";
        $fileNameSvg = str_replace('/', '-', $mimeType) . '.svg';

        $pathIconsFull = UtilSymfony::getContainer()->getParameter('kernel.root_dir') . "/../web" . $pathIconsWeb;

        if (file_exists($pathIconsFull . '/' . $fileNameSvg)) {
            return $pathIconsWeb . '/' . $fileNameSvg;
        }
        // try with 'gnome-mime-' prefix
        if (file_exists($pathIconsFull . '/' . 'gnome-mime-' . $fileNameSvg)) {
            return $pathIconsWeb . '/' . 'gnome-mime-' . $fileNameSvg;
        }
        // try generic eg image.svg
        $generic = explode('/', $mimeType)[0] . '.svg';
        if (file_exists($pathIconsFull . '/' . $generic)) {
            return $pathIconsWeb . '/' . $generic;
        }

        return $pathIconsWeb . '/' . 'unknown.svg';
    }




/// EB 5 VERSION
/// EB 5 VERSION
/// EB 5 VERSION
/// EB 5 VERSION
/// EB 5 VERSION
/// EB 5 VERSION


    // ----------------------------------------------------------------------------------------

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


    public static function calculateTextBox($text, $fontFile, $fontSize, $fontAngle = 0)
    {
        $rect = imagettfbbox($fontSize, $fontAngle, $fontFile, $text);
        $minX = min(array($rect[0], $rect[2], $rect[4], $rect[6]));
        $maxX = max(array($rect[0], $rect[2], $rect[4], $rect[6]));
        $minY = min(array($rect[1], $rect[3], $rect[5], $rect[7]));
        $maxY = max(array($rect[1], $rect[3], $rect[5], $rect[7]));

        return [
            "left" => abs($minX) - 1,
            "top" => abs($minY) - 1,
            "width" => $maxX - $minX,
            "height" => $maxY - $minY,
            "box" => $rect
        ];
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
     * renders text on an image file
     */
    public static function renderTextOnFile($pathSrc, $centerX01, $centerY01, $text, $textColor, $pathFont, $fontSizePercent, $pathDest)
    {
        // $colorInt = self::cssHexToInt($textColor);
        $colorInt = UtilColor::cssHexToInt($textColor);

        // ---- crop image
        $pathTmpCropped = '/tmp/' . uniqid('cropped-') . '.' . UtilFilesystem::getExtension($pathSrc);
        $pathSrc = self::cropImageByMetadata($pathSrc, $pathTmpCropped);

        // ---- load image
        $im = self::loadImage($pathSrc);


        // ---- calculate fontsize relative to image size...

        $srcImageWith = imagesx($im);
        $srcImageHeight = imagesy($im);

        $fontSizeInPx = min($srcImageWith, $srcImageHeight) / 100.0 * $fontSizePercent;
        $fontSizeInPt = ($fontSizeInPx * 3) / 4;


        // ---- calculate size of textbox because we write text centered at (px/py)

        $box = self::calculateTextBox($text, $pathFont, $fontSizeInPt);


        // ---- calc position

        $centerPosX = $centerX01 * $srcImageWith;
        $centerPosY = $centerY01 * $srcImageHeight;


        // ---- write text on image

        imagettftext($im, $fontSizeInPt, 0, $centerPosX - $box['width'] / 2, $centerPosY + +$box['height'] / 2, $colorInt, $pathFont, $text);

        self::saveImage($im, $pathDest);
    }


    /**
     * saves caption in xmp-metadata of an image
     * @param $pathImage
     * @param $captionText
     */
    public static function setImageCaption($pathImage, $captionText)
    {
        $image = \Mcx\Image\Image::fromFile($pathImage);
        $xmp = $image->getXmp();
        $xmp->setCaption($captionText);
        //$xmp->setHeadline('A test headline');
        //$xmp->setCopyright('Marc Christenfeldt');
        //$image->getIptc()->setCategory('Category');
        $image->save();
    }


    /**
     * get caption from xmp-metadata of image
     */
    public static function getImageCaption($pathImage)
    {
        $image = \Mcx\Image\Image::fromFile($pathImage);

        return $image->getXmp()->getCaption();
    }

    /**
     * saves crop in xmp-metadata of an image
     * @param $pathImage
     */
    public static function setImageCrop($pathImage, $x1, $y1, $x2, $y2)
    {
        $image = \Mcx\Image\Image::fromFile($pathImage);
        /** @var \Mcx\Image\Metadata\Xmp $xmp */
        $xmp = $image->getXmp();
        $xmp->setMcxCrop($x1, $y1, $x2, $y2);
        $image->save();
    }


    /**
     * get crop coordinates from xmp-metadata of image
     */
    public static function getImageCrop($pathImage)
    {
        $image = \Mcx\Image\Image::fromFile($pathImage);
        $xmp = $image->getXmp();

        return $xmp->getMcxCrop();
    }

    /**
     * TODO: more generic way to set ANY custom metadata
     *
     * saves caption in xmp-metadata of an image
     * @param $pathImage
     * @param $watermarkPosition
     */
    public static function setWatermarkPosition($pathImage, $watermarkPosition)
    {
        $image = \Mcx\Image\Image::fromFile($pathImage);
        $xmp = $image->getXmp();
        $xmp->setMcxWatermarkPosition($watermarkPosition);
        //$xmp->setHeadline('A test headline');
        //$xmp->setCopyright('Marc Christenfeldt');
        //$image->getIptc()->setCategory('Category');
        $image->save();
    }


    /**
     * get crop coordinates from xmp-metadata of image
     */
    public static function getWatermarkPosition($pathImage)
    {
        $image = \Mcx\Image\Image::fromFile($pathImage);
        $xmp = $image->getXmp();

        return $xmp->getMcxWatermarkPosition();
    }


    /**
     * @param string $fullPathImage
     * @param string $pathCropped
     * @param int $x1
     * @param int $y1
     * @param int $x2
     * @param int $y2
     */
    public static function cropImage($fullPathImage, $pathCropped, $x1, $y1, $x2, $y2)
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
     * crop by coordinates stored in xmp metadata (if any)
     *
     * @param $pathImage
     * @param $pathCropped
     * @return string path of cropped image or path of original image (if no cropping coordinates were found)
     */
    public static function cropImageByMetadata($pathImage, $pathCropped)
    {
        $crop = self::getImageCrop($pathImage);

        if (!empty($crop)) {
            UtilImage::cropImage($pathImage, $pathCropped, $crop['x1'], $crop['y1'], $crop['x2'], $crop['y2']);
            return $pathCropped;
        }

        return $pathImage;
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
     *
     * @param $pathSrc
     * @param $pathDest
     * @param $pathTag
     * @param $position
     */
    public static function watermarkImage($pathSrc, $pathDest, $pathTag, $position)
    {
        $imageTagger = new \WhyooOs\HelperClasses\ImageTagger();
        $imageTagger->tagImage($pathSrc, $pathTag, $position, 70, $pathDest); // fix the hardcoded size=70%
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
     * used by schlegel for stretching pdf-background to cover whole page
     *
     * resizes image to $dimension .. doesn't take care of aspect ratio - image is "stretched"
     * uses ImageWorkshop (composer require sybio/image-workshop)
     * 07/2017
     *
     * @param $pathSrc
     * @param $pathDest
     * @param array $dimensions [newWidth, newHeight]
     * @return bool
     */
    public static function resizeImage($pathSrc, $pathDest, array $dimensions)
    {
        $backgroundColor = 'ffffff';
        $layer = ImageWorkshop::initFromPath($pathSrc);
        $layer->resizeInPixel($dimensions[0], $dimensions[1]);
        $image = $layer->getResult($backgroundColor);
        return self::saveImage($image, $pathDest);
    }


}

