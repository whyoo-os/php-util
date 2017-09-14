<?php
/**
 * @author 2012 Marc Christenfeldt
 * @license MIT
 *
 * ase-format: http://www.selapa.net/couleurs/fileformats.php
 * Byte-order: Big-endian
 * all integers are unsigned
 */

define('BLOCK_TYPE_GROUP_START', 'c001');
define('BLOCK_TYPE_GROUP_END', 'c002');
define('BLOCK_TYPE_COLOR_ENTRY', '0001');
define('DBG', False);

/**
 * class for extracting colors from an .ase (Adobe Swatch Exchange) file
 */
class AseReader
{

    private $f = NULL; // file handle
    private $aColors = NULL;
    private $aNames = Null;

    /**
     * @param $r float 0..1 r value
     * @param $g float 0..1 g value
     * @param $b float 0..1 b value
     * @return string color as hex string ala #rrggbb
     */
    private function rgb2hex($r, $g, $b)
    {
        return sprintf('#%02x%02x%02x', round($r * 255), round($g * 255), round($b * 255));
    }

    /**
     * @param $c
     * @param $y
     * @param $m
     * @param $k
     * @return array
     */
    private function cymk2rgb($c, $y, $m, $k)
    {
        $r = (1 - $c) * (1 - $k);
        $g = (1 - $y) * (1 - $k);
        $b = (1 - $m) * (1 - $k);
        return [$r, $g, $b];
    }

    private function readcolor($colorModel)
    {
        if ($colorModel == 'RGB') {
            $r = $this->readfloat();
            $g = $this->readfloat();
            $b = $this->readfloat();
            return $this->rgb2hex($r, $g, $b);
        } else if ($colorModel == 'CMYK') {
            $c = $this->readfloat();
            $y = $this->readfloat();
            $m = $this->readfloat();
            $k = $this->readfloat();
            list($r, $g, $b) = $this->cymk2rgb($c, $y, $m, $k);
            return $this->rgb2hex($r, $g, $b);
        } else {
            throw new Exception("fixme: unimplemented color model '$colorModel'");
        }
    }

    private function readstring($length)
    {
        return fread($this->f, $length);
    }

    /**
     * return hexadecimal presentation string
     */
    private function readhex($length)
    {
        return bin2hex(fread($this->f, $length));
    }

    private function readint16()
    {
        $x = fread($this->f, 2);
        $y = unpack('n', $x);
        return $y[1];
    }

    /**
     * reads single precision binary floats (32bit)
     * author of conversation routine: info at forrest79 dot net
     */
    private function readfloat()
    {
        $bin = fread($this->f, 4);
        if ((ord($bin[0]) >> 7) == 0) $sign = 1;
        else $sign = -1;
        if ((ord($bin[0]) >> 6) % 2 == 1) $exponent = 1;
        else $exponent = -127;
        $exponent += (ord($bin[0]) % 64) * 2;
        $exponent += ord($bin[1]) >> 7;

        $base = 1.0;
        for ($k = 1; $k < 8; $k++) {
            $base += ((ord($bin[1]) >> (7 - $k)) % 2) * pow(0.5, $k);
        }
        for ($k = 0; $k < 8; $k++) {
            $base += ((ord($bin[2]) >> (7 - $k)) % 2) * pow(0.5, $k + 8);
        }
        for ($k = 0; $k < 8; $k++) {
            $base += ((ord($bin[3]) >> (7 - $k)) % 2) * pow(0.5, $k + 16);
        }

        $float = (float)$sign * pow(2, $exponent) * $base;
        return $float;
    }

    private function readint32()
    {
        $x = fread($this->f, 4);
        $y = unpack('N', $x);
        return $y[1];
    }

    private function readBlock()
    {
        $blockType = $this->readhex(2);
        $blockLength = $this->readint32();
        if (DBG) echo "BlockType:\t$blockType\n";
        if (DBG) echo "BlockLength:\t$blockLength\n";

        if ($blockType == BLOCK_TYPE_COLOR_ENTRY) {
            $nameLength = $this->readint16();
            $name = $this->readstring($nameLength * 2); //utf16 ?
            $this->aNames[] = $name;
            if (DBG) echo "NameLength:\t$nameLength\n";
            if (DBG) echo "Name:\t\t$name\n";
            $colorModel = trim($this->readstring(4));
            if (DBG) echo "ColorModel:\t$colorModel\n";
            $this->aColors[] = $this->readcolor($colorModel);
            $colorType = $this->readint16(); //   0 ⇒ Global, 1 ⇒ Spot, 2 ⇒ Normal
        } else {
            // just skip
            if (DBG) echo "skip..\n";
            $this->readstring($blockLength);
        }
    }

    /**
     * reads in the .ase file
     *
     * @param string $filename Filename of .ase file
     * @return void
     * @throws Exception
     */
    public function read($filename)
    {
        $this->aColors = [];
        $this->aNames = [];
        $this->f = fopen($filename, 'rb');

        $header = fread($this->f, 4);
        if ($header != 'ASEF') {
            throw new Exception("no ASE file header");
        }
        $version_major = $this->readint16();
        $version_minor = $this->readint16();
        if (DBG) echo "ASE Version:\t$version_major.$version_minor\n";
        $numBlocks = $this->readint32();
        if (DBG) echo "NumBlocks:\t$numBlocks\n";
        if (DBG) echo "Reading color information...\n";
        for ($i = 0; $i < $numBlocks; $i++) {
            $this->readBlock();
        }
        fclose($this->f);
    }

    /**
     * @return array Array of colors (hex-strings ala #aabbcc)
     */
    public function getPalette()
    {
        return $this->aColors;
    }

    /**
     * @return array Array of names of colors
     */
    public function getNames()
    {
        return $this->aNames;
    }
}


$a = new AseReader();
$a->read($argv[1]);
var_dump($a->getPalette());
var_dump($a->getNames());

