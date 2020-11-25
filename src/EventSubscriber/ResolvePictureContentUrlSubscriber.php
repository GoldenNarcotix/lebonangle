<?php


namespace App\EventSubscriber;


use ApiPlatform\Core\EventListener\EventPriorities;
use ApiPlatform\Core\Util\RequestAttributesExtractor;
use App\Entity\Picture;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Vich\UploaderBundle\Storage\StorageInterface;

class ResolvePictureContentUrlSubscriber implements EventSubscriberInterface
{
    private $storage;

    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['onPreSerialize', EventPriorities::PRE_SERIALIZE],
        ];
    }

    public function onPreSerialize(ViewEvent $event): void
    {
        $controllerResult = $event->getControllerResult();
        $request = $event->getRequest();

        if($controllerResult instanceof Response || !$request
                ->attributes
                ->getBoolean('_api_respond', true))
        {
            return;
        }

        if(!($attributes = RequestAttributesExtractor::extractAttributes($request)) ||
            !\is_a($attributes['resource_class'], Picture::class, true))
        {
            return;
        }

        $pictures = $controllerResult;

        if(!is_iterable($pictures))
        {
            $pictures = [$pictures];
        }

        foreach ($pictures as $picture)
        {
            if(!$picture instanceof Picture)
            {
                continue;
            }

            $picture->setContentUrl($this->storage->resolveUri($picture, 'file'));
        }
    }
}