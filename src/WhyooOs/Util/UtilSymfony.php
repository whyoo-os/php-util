<?php

namespace WhyooOs\Util;

use Symfony\Component\HttpFoundation\Response;

class UtilSymfony
{

    /**
     * hack to get container whereever needed
     *
     * @return \Symfony\Component\DependencyInjection\ContainerInterface|null
     */
    public static function getContainer()
    {
        // final solution: use global $kernel
        global $kernel;
        if (is_null($kernel)) {
            return null;
        }

        return $kernel->getContainer();
    }





    /**
     * TODO: use
     *       BinaryFileResponse::trustXSendfileTypeHeader(); // "the X-Sendfile-Type header should be trusted" (?)
     *      return new BinaryFileResponse($pathImageFiltered);
     */
    public static function createImageResponse($filename)
    {
        // Generate response
        $response = new Response();

        // headers .. cache for one day
        #$response->headers->set('Cache-Control', 'private');
        #$response->headers->set('Content-Disposition', 'attachment; filename="' . basename($filename) . '";');
        $response->headers->set('Content-Type', mime_content_type($filename));
        $response->headers->set('Content-Length', filesize($filename));
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'max-age=86400, public');
        $response->headers->set('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + 86400));

        // Send headers before outputting anything
        $response->sendHeaders();

        $response->setContent(readfile($filename));

        return $response;
    }



}