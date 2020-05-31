<?php

namespace App\EventSubscriber;

use App\Entity\EntityImage;
use DateTime;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

class ImageDeleteSubscriber implements EventSubscriber
{
    private $params;
    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::postRemove,
            Events::preUpdate
        ];
    }

    public function postRemove(LifecycleEventArgs $args)
    {
        $object = $args->getObject();

        if ($object instanceof EntityImage) {
            $filesystem = new Filesystem();
            $filesystem->remove($this->params->get('kernel.project_dir').'/public'.$object->getImage());
        }
    }

    public function preUpdate(PreUpdateEventArgs $args)
    {
        $object = $args->getObject();
        if ($object instanceof EntityImage) {
            
            $changeSet = $args->getEntityChangeSet();
            
            if (isset($changeSet['image'][0])) 
            {
                $oldImage = $changeSet['image'][0];
                $filesystem = new Filesystem();
                $filesystem->remove($this->params->get('kernel.project_dir').'/public'.$oldImage);

            }
        }
    }
}