<?php

namespace App\EventSubscriber;

use App\Entity\Restaurant;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

class SetRoleRestaurateurSubscriber implements EventSubscriber
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

        if ($object instanceof Restaurant) {
            $membre = $object->getMembres();
            $membreRoles = $membre->getRoles();
            if (!in_array('ROLE_RESTAURATEUR', $membreRoles)) {
                $membreRoles[] = 'ROLE_RESTAURATEUR';
                $membre->setRoles($membreRoles);
            }
        }
    }
}
