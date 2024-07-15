# Api platform Doctrine

> This repo shows how to configure several subdomains and how to have several databases depending on the subdomain.

based on: https://carlos-compains.medium.com/multi-database-doctrine-symfony-based-project-0c1e175b64bf

1. If not already done, [install Docker Compose](https://docs.docker.com/compose/install/) (v2.10+)
2. Run `docker compose build --no-cache` to build fresh images
3. Run `docker compose up --pull always -d --wait` to start the project
4. Open `https://localhost` in your favorite web browser and [accept the auto-generated TLS certificate](https://stackoverflow.com/a/15076602/1352334)
5. Run `docker compose down --remove-orphans` to stop the Docker containers.

> Be sure to declare subdomains on your machine to redirect them to the project's localhost

```shell
##
# Host Database
#
# localhost is used to configure the loopback interface
# when the system is booting.  Do not change this entry.
##
127.0.0.1       subdomain1.dev.local
127.0.0.1       subdomain2.dev.local

#...
255.255.255.255 broadcasthost
::1             localhost
```

## Create certificates

```bash
mkcert subdomain1.dev.local
mkcert subdomain2.dev.local
```

## Load fixtures

`docker compose exec php php bin/console d:s:u --force`
`docker compose exec php php bin/console doctrine:fixtures:load --no-interaction`

## Update databases
`docker compose exec php php bin/console app:create-database app_subdomain1`
`docker compose exec php php bin/console app:create-database app_subdomain2`
`docker compose exec php php bin/console app:database:update --all`

### Dev mode - update all databases force without migrations
`docker compose exec php php bin/console app:database:update --dev-force-all`

### Dev mode - load fixtures on all databases
`docker compose exec php php bin/console app:database:update --load-fixtures`

> Attention: works only with databases prefixed by `app_`

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
