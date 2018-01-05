<?php

namespace WhyooOs\Util;

use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class UtilSymfony
{

    /**
     * hack to get container where ever needed
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
     * convenience hack
     * 01/2018
     * ebayGen
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
     * ebayGen
     *
     * @return mixed the requested parameter from the container
     */
    public static function getParameter(string $name)
    {
        return self::getContainer()->getParameter($name);
    }


    /**
     * when using this be sure that there is no way to get some file like /etc/passwd ..
     * @see UtilFilesystem::sanitizeFilename()
     *
     * @param string $pathFile
     * @return Response
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
     * alternative / faster version of self::createImageResponse ..
     *
     * when using this be sure that there is no way to get some file like /etc/passwd ..
     * @see UtilFilesystem::sanitizeFilename()
     *
     *
     * @param string $pathFile
     * @return BinaryFileResponse
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
        if( !is_array($data) && !is_object($data)) {
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
     * @param null|array|string $groups Serialization Group Names
     * @return SerializationContext
     */
    public static function getSerializationContext($groups = null)
    {
        $context = SerializationContext::create();
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


    /**
     * returns currently logged in user (if any) or null (if no user logged in)
     * @return UserInterface
     */
    public static function getUser()
    {
        if ($token = self::getContainer()->get('security.token_storage')->getToken()) {
            return $token->getUser();
        } else {
            return null;
        }
    }

    /**
     * orig: https://ourcodeworld.com/articles/read/459/how-to-authenticate-login-manually-an-user-in-a-controller-with-or-without-fosuserbundle-on-symfony-3
     * @param UserInterface $user
     */
    public static function loginUser(UserInterface $user, $firewallName = 'secured_area')
    {
        $token = new UsernamePasswordToken(
            $user,
            null,
            $firewallName,
            $user->getRoles());

        self::getContainer()->get('security.token_storage')->setToken($token);

        self::getContainer()->get('session')->set('_security_' . $firewallName, serialize($token));

//        // Fire the login event manually
//        $event = new InteractiveLoginEvent($request, $token);
//        self::getContainer()->get("event_dispatcher")->dispatch("security.interactive_login", $event);
    }

}