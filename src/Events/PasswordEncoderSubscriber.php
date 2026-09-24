<?php

namespace App\Events;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use Override;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class PasswordEncoderSubscriber implements EventSubscriberInterface{
    #[Override]
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['encodePassword', EventPriorities::PRE_WRITE]
            ];
    }

    public function encodePassword($event)
    {
        //LAAAAAAAAAAAAAA
    }
}