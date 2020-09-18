<?php

namespace WhyooOs\Util;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * for projects using twig v2.x
 *
 * 09/2020
 */
class UtilTwigV2
{

    /**
     * attention: could be security threat if you use some user-input as part of template path
     *
     * 09/2020
     *
     * @param string $pathAbsTemplate
     * @param array $vars
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public static function renderTemplate(string $pathAbsTemplate, array $vars)
    {
        $loader = new FilesystemLoader(dirname($pathAbsTemplate));
        $twig = new Environment($loader);
        $template = $twig->load(basename($pathAbsTemplate));

        return $template->render($vars);
    }

}
