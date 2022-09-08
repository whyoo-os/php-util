<?php

namespace WhyooOs\Util;

use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

/**
 * 01/2022 created
 */
class UtilSymfonyV6
{

    /**
     * for symfony v6
     * hack to get container where ever needed, using global $kernel
     *
     * @return \Symfony\Component\DependencyInjection\ContainerInterface|null
     */
    public static function getContainer()
    {
        if (isset($GLOBALS['app']) && $GLOBALS['app'] instanceof \Symfony\Bundle\FrameworkBundle\Console\Application) {
            // old sf
            return $GLOBALS['app']->getKernel()->getContainer();
        } elseif (isset($GLOBALS['kernel']) && $GLOBALS['kernel'] instanceof \App\Kernel) {
            // new sf
            return $GLOBALS['kernel']->getContainer();
        } else {
            throw new \Exception("FAIL...");
        }
    }

    /**
     * convenience hack
     * 01/2018
     * cloudlister
     *
     * @return mixed the service from the container
     */
    public static function getService(string $serviceId)
    {
        return self::getContainer()->get($serviceId);
    }


    /**
     * convenience hack
     * 01/2018
     * cloudlister
     *
     * @return mixed the requested parameter from the container
     */
    public static function getParameter(string $name)
    {
        return self::getContainer()->getParameter($name);
    }


    /**
     * when using this be sure that there is no way to get some file like /etc/passwd ..
     * @param string $pathFile
     * @return Response
     * @see UtilFilesystem::sanitizeFilename()
     *
     */
    public static function createImageResponse(string $pathFile)
    {
        // Generate response
        $response = new Response();

        // headers .. cache for one day
        #$response->headers->set('Cache-Control', 'private');
        #$response->headers->set('Content-Disposition', 'attachment; filename="' . basename($filename) . '";');
        $response->headers->set('Content-Type', mime_content_type($pathFile));
        $response->headers->set('Content-Length', filesize($pathFile));
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'max-age=86400, public');
        $response->headers->set('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + 86400));

        // Send headers before outputting anything
        $response->sendHeaders();

        $response->setContent(readfile($pathFile));

        return $response;
    }

    /**
     * 01/2018
     * could be optimized
     *
     * @param string $pathFile
     * @return Response
     */
    public static function createPdfResponse(string $pathFile)
    {
        // Generate response
        $response = new Response();

        // headers .. do NOT cache
        $response->headers->set('Content-Disposition', 'attachment; filename="' . basename($pathFile) . '";');
        $response->headers->set('Content-Type', mime_content_type($pathFile));
        $response->headers->set('Content-Length', filesize($pathFile));
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Expires', gmdate('D, d M Y H:i:s \G\M\T', time()));

        $response->sendHeaders(); // FIXME .. really?
        $response->setContent(readfile($pathFile));

        return $response;
    }


    /**
     * 08/2018 used for sending svg in cloudlister
     *
     * @param string $strSvg
     * @return Response
     */
    public static function createSvgResponse(string $strSvg)
    {
        $response = new Response($strSvg);
        $response->headers->set('content-type', 'image/svg+xml');

        return $response;
    }


    /**
     * alternative / faster version of self::createImageResponse ..
     *
     * when using this be sure that there is no way to get some file like /etc/passwd ..
     * @param string $pathFile
     * @return BinaryFileResponse
     * @see UtilFilesystem::sanitizeFilename()
     *
     *
     */
    public static function createFileResponse(string $pathFile)
    {
        BinaryFileResponse::trustXSendfileTypeHeader(); // "the X-Sendfile-Type header should be trusted" (?)

        return new BinaryFileResponse($pathFile);
    }


    /**
     * using JMS serializer
     *
     * @param mixed $data
     * @param null|array|string $groups
     * @return array
     */
    public static function toArray($data, $groups = null)
    {
        if (!is_array($data) && !is_object($data)) {
            return $data;
        }
        $serializationContext = self::getSerializationContext($groups);
        $serializer = self::getContainer()->get('jms_serializer');

        $arr = $serializer->toArray($data, $serializationContext);

        // ---- add serializion groups to array for debugging
//        if( !is_null($groups)) {
//            $arr['__groups'] =  $serializationContext->attributes->get('groups');
//        }

        return $arr;
    }


    /**
     * 06/2018 added $context->setSerializeNull(true)
     *
     * @param null|array|string $groups Serialization Group Names
     * @return SerializationContext
     */
    public static function getSerializationContext($groups = null)
    {
        $context = SerializationContext::create();
        $context->setSerializeNull(true);
        $context->enableMaxDepthChecks();

        if (!is_null($groups)) {
            if (is_string($groups)) {
                $groups = [$groups];
            }
            // $groups[] = "formatters"; // HACK
            $groups[] = "ALWAYS"; // HACK
            $context->setGroups($groups);
        }

        return $context;
    }


//    /**
//     * returns currently logged in user (if any) or null (if no user logged in)
//     * @return UserInterface
//     */
//    public static function getUser()
//    {
//        #if ($token = self::getContainer()->get('Symfony\Component\Security\Core\Authentication\Token\Storage\UsageTrackingTokenStorage')->getToken()) {
//        if ($token = self::getContainer()->get('security.token_storage')->getToken()) {
//            return $token->getUser();
//        } else {
//            return null;
//        }
//    }


    /**
     * orig: https://ourcodeworld.com/articles/read/459/how-to-authenticate-login-manually-an-user-in-a-controller-with-or-without-fosuserbundle-on-symfony-3
     * @param UserInterface $user
     */
    public static function loginUser(UserInterface $user, $firewallName = 'secured_area')
    {
        $token = new UsernamePasswordToken(
            $user,
            $firewallName,
            $user->getRoles());

        self::getContainer()->get('security.token_storage')->setToken($token);
        try {
            self::getContainer()->get('session')->set('_security_' . $firewallName, serialize($token));
        } catch (\Exception $e) {
            // ignore RuntimeException: Failed to start the session because headers have already been sent
        }

//        // Fire the login event manually
//        $event = new InteractiveLoginEvent($request, $token);
//        self::getContainer()->get("event_dispatcher")->dispatch("security.interactive_login", $event);
    }


    /**
     * 03/2018
     * @return string the symfony environment, eg. "dev", "prod", ...
     */
    public static function getEnvironment()
    {
        return self::getService('kernel')->getEnvironment();
    }


    /**
     * 05/2018
     * used eg to add the route name to serialization group
     */
    public static function getRouteName()
    {
        $request = self::getService('request_stack')->getCurrentRequest();
        $routeName = $request->get('_route');

        return $routeName;
    }

}
