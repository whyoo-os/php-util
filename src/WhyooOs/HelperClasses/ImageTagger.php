<?php

namespace WhyooOs\HelperClasses;

use WhyooOs\Util\UtilImage;


/**
 * class for adding watermarks (logos) to images
 */
class ImageTagger
{

    const ALLOWED_POSITIONS = ['TL', 'TC', 'TR', 'ML', 'MC', 'MR', 'BL', 'BC', 'BR', 'AT', 'AB', 'AL', 'AR'];

    /**
     * private helper
     * append image at t, b, l or r
     * only 100% scale possible
     *
     * @param $pathImage
     * @param $pathTag
     * @param $tagPosition
     * @param $pathDest
     */
    private function _appendToImage($pathImage, $pathTag, $tagPosition, $pathDest)
    {
        $sizeSrc = getimagesize($pathImage);
        $widthSrc = $sizeSrc[0];
        $heightSrc = $sizeSrc[1];
        $sizeTag = getimagesize($pathTag);
        $widthTag = $sizeTag[0];
        $heightTag = $sizeTag[1];

        // load images
        $imImage = UtilImage::loadImage($pathImage);
        $imTag = UtilImage::loadImage($pathTag);


        if ($tagPosition == "T" || $tagPosition == "B") {
            $scaleTag = (float)$widthSrc / $widthTag;
            // W/H of Dest
            $newWidth = $widthSrc;
            $newHeight = $heightSrc + $heightTag * $scaleTag;

            $dstXImage = 0;
            $dstYImage = $tagPosition == 'T' ? $heightTag * $scaleTag : 0;

            $dstXTag = 0;
            $dstYTag = $tagPosition == 'T' ? 0 : $heightSrc;
        } else { // L or R
            $scaleTag = (float)$heightSrc / $heightTag;
            $newWidth = $widthSrc + $widthTag * $scaleTag;
            $newHeight = $heightSrc;

            $dstXImage = $tagPosition == 'L' ? $widthTag * $scaleTag : 0;
            $dstYImage = 0;

            $dstXTag = $tagPosition == 'L' ? 0 : $widthSrc;
            $dstYTag = 0;
        }

        // create empty image
        $imDest = imagecreatetruecolor($newWidth, $newHeight);
#	imagealphablending( $imDest, true);
#	imagesavealpha( $imDest, true);
        imagefill($imDest, 0, 0, 0xff0000);

        // copy image (not resized)
        imagecopy($imDest, $imImage, $dstXImage, $dstYImage, 0, 0, $widthSrc, $heightSrc);
        // copy tag (resampled)
        imagecopyresampled($imDest, $imTag, $dstXTag, $dstYTag, 0, 0, $widthTag * $scaleTag, $heightTag * $scaleTag, $widthTag, $heightTag);
        UtilImage::saveImage($imDest, $pathDest);
    }


    /**
     * position:
     * TL TC TR
     * ML MC MR
     * BL BC BR
     *
     *     AT
     * AL      AR
     *     AB
     *
     *
     * @param $pathImage
     * @param $pathTag
     * @param $position
     * @param $inScale 1..100%
     * @param $pathDest
     * @throws \Exception
     */
    public function tagImage(string $pathImage, string $pathTag, string $position, $inScale, string $pathDest)
    {

        // 0) check if we know the position parameter
        if (!in_array($position, self::ALLOWED_POSITIONS)) {
            throw new \Exception("position $position not allowed. allowed are: " . implode(', ', self::ALLOWED_POSITIONS));
        }

#file_get_contents( $pathImage);
//var_dump( $pathImage); die( "ppppppppppppppppppppp");
//$pathTag = $pathImage;
        // 0.5) get image sizes
        $sizeSrc = getimagesize($pathImage);
        $widthSrc = $sizeSrc[0];
        $heightSrc = $sizeSrc[1];
        $sizeTag = getimagesize($pathTag);
//        dump(func_get_args()); die();

        $widthTag = $sizeTag[0];
        $heightTag = $sizeTag[1];


        // 1) decise mode: scaleByWidth or scaleByHeight --> scaleFatcor
        if (in_array($position, ['TL', 'TR', 'BL', 'BR'])) { // ecke
            // in einer der ecken --> entweder in die höhe oder breite skalieren, abhängig von den abmaßen des tag-images
            $mode = $widthTag > $heightTag ? 'W' : 'H';
        } elseif (in_array($position, ['ML', 'MR'])) { // mitte links oder rechts
            // wenn scale=100%, soll die gesamte kante abgedeckt sein ... mode = H
            $mode = 'H';
        } elseif (in_array($position, ['TC', 'BC'])) { // oben oder unten zentriert
            $mode = 'W';
        } elseif (in_array($position, ['MC'])) { // in der mitte
            $ratioSrc = (float)$widthSrc / $heightSrc;
            $ratioTag = (float)$widthTag / $heightTag;
            if ($ratioSrc > $ratioTag) {
                $mode = 'H';
            } else {
                $mode = 'W';
            }
        } elseif ($position[0] == "A") {
            $this->_appendToImage($pathImage, $pathTag, $position[1], $pathDest);
            return;
        } else {
            throw new \Exception("unknown position $position");
        }


        $topMiddleBottom = $position[0];
        $leftCenterRight = $position[1];
#var_dump( $leftCenterRight);
#var_dump( $topMiddleBottom);


        // 2) scale watermark
        if ($mode == 'W') {
            $scaleFactor = (float)$widthSrc / $widthTag;
        } elseif ($mode == 'H') {
            $scaleFactor = (float)$heightSrc / $heightTag;
        } else {
            throw new \Exception('mode was undefined');
        }

        $scaleFactor *= ($inScale / 100.0);

        $newW = $widthTag * $scaleFactor;
        $newH = $heightTag * $scaleFactor;


        // 3) imcopy to src: left/right/centerb .. scaleFactor * $inScale
        $imDest = UtilImage::loadImage($pathImage);
        $imTag = UtilImage::loadImage($pathTag);

        // dstX
        if ($leftCenterRight == 'L') {
            $dstX = 0;
        } elseif ($leftCenterRight == 'R') {
            $dstX = $widthSrc - $newW;
        } else { // center
            $dstX = ($widthSrc - $newW) / 2; // fixme?
        }

        // dstY
        if ($topMiddleBottom == 'T') {
            $dstY = 0;
        } elseif ($topMiddleBottom == 'B') {
            $dstY = $heightSrc - $newH + 1;
        } else { // middle
            $dstY = ($heightSrc - $newH) / 2; // FIXME?
        }

        // rounding
        $dstX = round($dstX);
        $dstY = round($dstY);
        $newW = round($newW);
        $newH = round($newH);

        imagealphablending($imDest, true);
        imagesavealpha($imDest, true);
        imagecopyresampled($imDest, $imTag, $dstX, $dstY, 0, 0, $newW, $newH, $widthTag, $heightTag);
//print( "imagecopyresampled( $dstX , $dstY , 0,0 , $newW, $newH , $widthTag, $heightTag);\n");
        // return img.
        #print "save to $pathDest\n";
        UtilImage::saveImage($imDest, $pathDest);
    }

}
