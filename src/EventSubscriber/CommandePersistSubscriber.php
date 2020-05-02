<?php

namespace App\EventSubscriber;

use App\Entity\Commande;
use DateInterval;
use DateTime;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Swift_Mailer;
use Swift_Message;

class CommandePersistSubscriber implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return [
            Events::prePersist
        ];
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $object = $args->getObject();

        if ($object instanceof Commande) {
            $object->setDateAchat(new DateTime());

            $dateRecpetion = new DateTime();
            $dateRecpetion->add(new DateInterval('PT1H'));
            $object->setDateReception($dateRecpetion);
        }
    }
}
