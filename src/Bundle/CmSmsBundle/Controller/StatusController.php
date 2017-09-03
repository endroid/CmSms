<?php

/*
 * (c) Jeroen van den Enden <info@endroid.nl>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Endroid\CmSms\Bundle\CmSmsBundle\Controller;

use Doctrine\ORM\EntityManager;
use Endroid\CmSms\Bundle\CmSmsBundle\Entity\Message;
use Endroid\CmSms\Bundle\CmSmsBundle\Entity\Status;
use Endroid\CmSms\Bundle\CmSmsBundle\Exception\InvalidStatusDataException;
use Endroid\CmSms\Bundle\CmSmsBundle\Repository\MessageRepository;
use Endroid\CmSms\Client;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Endroid\CmSms\Message as DomainMessage;
use Endroid\CmSms\Status as DomainStatus;
use JMS\Serializer\SerializerBuilder;

/**
 * @Route("/status")
 */
class StatusController extends Controller
{
    /**
     * @Route("/update", name="endroid_cm_sms_status_update")
     *
     * @param Request $request
     * @return Response
     */
    public function updateStatusAction(Request $request)
    {
        // Support both GET and POST
        $data = $request->getMethod() === Request::METHOD_GET ? $request->query->all() : $request->request->all();

        try {
            $status = DomainStatus::fromWebHookData($data);
        } catch (InvalidStatusDataException $exception) {
            return new Response();
        }

        /** @var Message $message */
        $message = $this->getMessageRepository()->find($status->getMessageId());

        if (!$message instanceof Message) {
            return new Response();
        }

        $message->addStatus(Status::fromDomain($status));
        $this->getMessageRepository()->save($message);

        return new Response();
    }

    /**
     * @return MessageRepository
     */
    protected function getMessageRepository()
    {
        return $this->getDoctrine()->getRepository('EndroidCmSmsBundle:Message');
    }
}
