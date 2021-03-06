<?php

namespace Doctrine\ORM\ODMAdapter\Event;

use Doctrine\Common\Persistence\Event\ManagerEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnClearEventArgs;

class OrmLifecycleListener extends AbstractListener
{
    public function prePersist(LifecycleEventArgs $event)
    {
        $object = $event->getEntity();
        if ($this->isReferenceable($object)) {
            #print("ORM persist\n");
            $this->objectAdapterManager->persistReference($object);
        }

    }

    public function preUpdate(LifecycleEventArgs $event)
    {
        $object = $event->getObject();
        if ($this->isReferenceable($object)) {
            #$this->objectAdapterManager->persistReference($object);
        }
    }

    public function postLoad(LifecycleEventArgs $event)
    {
        $object = $event->getObject();
        if ($this->isReferenceable($object)) {
            $this->objectAdapterManager->findReference($object);
        }
    }

    public function preRemove(LifecycleEventArgs $event)
    {
        $object = $event->getObject();
        if ($this->isReferenceable($object)) {
            $this->objectAdapterManager->removeReference($object);
        }
    }

    public function onClear(OnClearEventArgs $event)
    {
        $this->objectAdapterManager->clear();
    }

    public function preFlush(PreFlushEventArgs $event)
    {
        $this->objectAdapterManager->flushReference();
    }
}
