<?php

namespace App\EventSubscriber;

use App\Entity\EntityImage;
use DateTime;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
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
            Events::postRemove
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
}