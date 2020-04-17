<?php

/*
 * This file is part of the DubtureCustomerIOBundle package.
 *
 * (c) Robert Gruendler <robert@dubture.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dubture\CustomerIOBundle\Tracking;

use Customerio\Client;
use Dubture\CustomerIOBundle\Event\ActionEvent;
use Dubture\CustomerIOBundle\Event\TrackingEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class EventTracker
 * @package Tracking
 */
class EventTracker implements EventSubscriberInterface
{
    /**
     * @var Client
     */
    private $api;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Client $client
     * @param LoggerInterface $logger
     */
    public function __construct(Client $api, LoggerInterface $logger)
    {
        $this->api = $api;
        $this->logger = $logger;
    }

    /**
     * @param TrackingEvent $event
     * @param $name
     * @param EventDispatcherInterface $dispatcher
     */
    public function onIdentify(TrackingEvent $event, $name, EventDispatcherInterface $dispatcher)
    {
        $customer = $event->getCustomer();

        $dispatcher->dispatch('customerio.beforeCreateCustomer', $event);

        $this->logger->info('Sending createCustomer request to customer.io with id '
                . $customer->getId(), $customer->getAttributes());

        $options = ['id' => $customer->getId(),'email' => $customer->getEmail()];
        $options = array_merge($options, $customer->getAttributes());

        $response = $this->api->customers->add($options);

        if (!$response->success()) {
            throw new BadRequestHttpException($response->message());
        }
    }

    /**
     * @param ActionEvent $event
     * @param $name
     * @param EventDispatcherInterface $dispatcher
     */
    public function onAction(ActionEvent $event, $name, EventDispatcherInterface $dispatcher)
    {
        $dispatcher->dispatch('customerio.beforeFireEvent', $event);

        $this->logger->info('Firing customerio event '
                . $event->getAction(), $event->getAttributes());

        $options = ['action' => $event->getAction()];
        $options = array_merge($options, $event->getAttributes());

        $response = $this->api->events->anonymous($options);

        if (!$response->success()) {
            throw new BadRequestHttpException($response->message());
        }
    }

    /**
     * @param Api $api
     */
    public function setApi(Client $api)
    {
        $this->api = $api;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
                TrackingEvent::IDENTIFY => 'onIdentify',
                TrackingEvent::ACTION   => 'onAction',
        );
    }
}