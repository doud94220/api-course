<?php

/*
    Classe créée à la main par nous, mais ça marche pas sur les versions récente de SF...
    => On va donc créer un "State Processor" dans un nouveau dossier "State"
*/

namespace App\Events;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\User;
use Override;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PasswordEncoderSubscriber_obsolete implements EventSubscriberInterface{

/**
 * Encoder de mdp
 *
 * @var UserPasswordHasherInterface //Permet d'avoir l'autocomplétion
 */
    private $encoder;

    public function __construct(UserPasswordHasherInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    #[Override]
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['encodePassword', EventPriorities::PRE_WRITE]
            //encodePassword = nom de la fonction qu'on va appeler
            //Voir https://api-platform.com/docs/v3.2/core/events/ pour comprendre les évènements du Kernel
            /*
                Vérifier si Symfony détecte bien le Subscriber :
                php bin/console debug:event-dispatcher kernel.view
            */
            ];
    }

    public function encodePassword(ViewEvent $event)
    {
        dd('ici'); //On ne rentre pas dans la fonction...

        $user = $event->getControllerResult(); //On récupère le User

        dd($user);

        $method = $event->getRequest()->getMethod();

        if ($user instanceof User && $method === "POST")
        {
            $hash = $this->encoder->hashPassword($user, $user->getPassword());
            $user->setPassword($hash);
        }
    }
}