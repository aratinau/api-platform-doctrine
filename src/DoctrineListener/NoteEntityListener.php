<?php

namespace App\DoctrineListener;

use App\Entity\Conference;
use App\Entity\Note;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\String\Slugger\SluggerInterface;
use Doctrine\ORM\Events;


#[AsEntityListener(event: Events::prePersist, entity: Note::class)]
#[AsEntityListener(event: Events::preUpdate, entity: Note::class)]
class NoteEntityListener
{
    public function __construct(
        private SluggerInterface $slugger,
    ) {
    }

    public function prePersist(Note $note, LifecycleEventArgs $event)
    {
        $note->setLog('This is from prePersist');
    }

    public function preUpdate(Note $note, LifecycleEventArgs $event)
    {
        $note->setLog('This is from preUpdate');
    }
}
