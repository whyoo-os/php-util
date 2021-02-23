<?php

namespace WhyooOs\Util;

use TitasGailius\Terminal\Terminal;
use WhyooOs\Util\UtilStringArray;

/**
 * composer require titasgailius/terminal
 * apt install poppler-utils

 * 02/2021 created (for slidesmailer)
 *
 */
class UtilPdf
{

    /**
     * composer require titasgailius/terminal
     * 02/2021 created
     *
     * @param string $cmd
     * @return \TitasGailius\Terminal\Response
     */
    public static function run(string $cmd): \TitasGailius\Terminal\Response
    {
        return Terminal::run($cmd);
    }

    /**
     * composer require titasgailius/terminal
     * apt install poppler-utils
     *
     * 02/2021 created
     *
     * @param string $pathPdfFile
     */
    public static function countPages(string $pathPdfFile): int
    {
        /*
            # Using pdfinfo (fastest) - apt install poppler-utils
            pdfinfo $PATH_PDF | awk '/^Pages:/ {print $2}'

            # Using pdftk:
            pdftk $PATH_PDF dump_data | grep NumberOfPages | awk '{print $2}'

            # using pdftotext
            pdftotext $PATH_PDF - | grep -c $'\f'

            # using imagemagick's identify (slowest)
            identify "$PATH_PDF" 2>/dev/null | wc -l | tr -d ' '
        */

        return (int)(self::pdfInfo($pathPdfFile)['Pages']);
    }

    /**
     * apt install poppler-utils
     *
     * 02/2021 created
     *
     * @param string $pathPdfFile
     * @return array the info printed by pdfinfo as a dictionary
     */
    public static function pdfInfo(string $pathPdfFile): array
    {
        $cmd = "pdfinfo $pathPdfFile";

        $lines = UtilStringArray::trimExplode("\n", self::run($cmd)->output());

        $info = array_map(function($line){
            return UtilStringArray::trimExplode(':', $line, 2);
        }, $lines);

        $map = array_combine(array_column($info, 0), array_column($info, 1));

        // UtilDebug::dd($info, $lines, $map);

        return $map;
    }


}


