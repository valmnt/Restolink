<?php

namespace App\EventSubscriber;

use App\Commande\CommandeParameter;
use App\Entity\Commande;
use DateInterval;
use DateTime;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

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

            $object->setStatus(0);

            $commandeParameter = new CommandeParameter();
            $object->setFraisLivraison($commandeParameter->getFraislivraison());
        }
    }
}
