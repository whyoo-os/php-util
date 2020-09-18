<?php

namespace WhyooOs\Util;

use Twig_Environment;
use Twig_Loader_Filesystem;

/**
 * for projects using twig v1.x
 *
 * 09/2020
 */
final class UtilTwigV1
{
    /**
     * attention: could be security threat if you use some user-input as part of template path
     *
     * 09/2020
     *
     * @param string $pathAbsTemplate
     * @param array $vars
     * @return string
     */
    public static function renderTemplate(string $pathAbsTemplate, array $vars)
    {
        $loader = new Twig_Loader_Filesystem([''], dirname($pathAbsTemplate));
        $twig = new Twig_Environment($loader);
        $template = $twig->loadTemplate(basename($pathAbsTemplate));

        return $template->render($vars);
    }
}
