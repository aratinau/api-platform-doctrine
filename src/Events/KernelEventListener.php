<?php

namespace App\Events;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\KernelInterface;

class KernelEventListener
{
    /**
     * @param ManagerRegistry $doctrine
     * @param KernelInterface $kernel
     */
    public function __construct(
        private ManagerRegistry $doctrine,
        private KernelInterface $kernel
    ) {
    }

    /**
     * @param KernelEvent $event
     * @return void
     * @throws \Exception
     */
    #[AsEventListener(event: KernelEvents::REQUEST)]
    public function onRequest(KernelEvent $event): void
    {
        $dbName = $this->getDatabaseNameFromOrigin($event);

        if (in_array($dbName, $this->doctrine->getConnection()->getDatabases()))
        {
            $this->doctrine->getConnection()->changeDatabase($dbName);
        } else {
            if ($this->kernel->getEnvironment() === 'prod') {
                throw new \Exception('No database available for given origin');
            }
        }
    }

    /**
     * @param Request $request
     * @return string
     */
    private function getDatabaseNameFromOrigin(KernelEvent $event): string
    {
        $request = $event->getRequest();
        $currentHost = $request->getHost();

        $dbByHost = explode('.',$currentHost)[0];

        return 'app_' . $dbByHost;
    }
}
