# Api platform Doctrine

1. If not already done, [install Docker Compose](https://docs.docker.com/compose/install/) (v2.10+)
2. Run `docker compose build --no-cache` to build fresh images
3. Run `docker compose up --pull always -d --wait` to start the project
4. Open `https://localhost` in your favorite web browser and [accept the auto-generated TLS certificate](https://stackoverflow.com/a/15076602/1352334)
5. Run `docker compose down --remove-orphans` to stop the Docker containers.

## Load fixtures

`make sh` to enter the container
`php bin/console doctrine:fixtures:load`

## Note Listener

```php
<?php

namespace App\DoctrineListener;

use App\Entity\Conference;
use App\Entity\Note;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;


#[AsEntityListener(event: Events::prePersist, entity: Note::class)]
#[AsEntityListener(event: Events::preUpdate, entity: Note::class)]
class NoteEntityListener
{
    public function prePersist(Note $note, LifecycleEventArgs $event)
    {
        $note->setLog('This is from prePersist');
    }

    public function preUpdate(Note $note, LifecycleEventArgs $event)
    {
        $note->setLog('This is from preUpdate');
    }
}

```
