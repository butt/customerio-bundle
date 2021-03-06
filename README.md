# customer.io bundle

[![Build Status](https://travis-ci.org/pulse00/customerio-bundle.svg?branch=master)](https://travis-ci.org/pulse00/customerio-bundle)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/pulse00/customerio-bundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/pulse00/customerio-bundle/?branch=master)

[![Latest Stable Version](https://poser.pugx.org/dubture/customerio-bundle/v/stable.svg)](https://packagist.org/packages/dubture/customerio-bundle) [![Total Downloads](https://poser.pugx.org/dubture/customerio-bundle/downloads.svg)](https://packagist.org/packages/dubture/customerio-bundle) [![License](https://poser.pugx.org/dubture/customerio-bundle/license.svg)](https://packagist.org/packages/dubture/customerio-bundle)



Symfony integration for http://customer.io.


## Configuration

Install the bundle using composer and register it in your Kernel.

Then configure your `site_id`  and `api_key`:


```yml
# app/config/config.yml

dubture_customer_io:
  site_id: <YOUR-SITE-ID>
  api_key: <YOUR-API-KEY>

```

## Usage

### Customer model

Implement `Dubture\CustomerIOBundle\Model\CustomerInterface` on your customer domain class.

### Event Tracking / Customer identification

```php

use Dubture\CustomerIOBundle\Event\TrackingEvent;
use Dubture\CustomerIOBundle\Event\ActionEvent;

/** @var \Symfony\Component\EventDispatcher\EventDispatcher $tracker */
$dispatcher = $this->getContainer()->get('event_dispatcher');

$customer = $someRepo->getCustomer(); // retrieve your customer domain object

// send the customer over to customer.io for identification
$dispatcher->dispatch(TrackingEvent::IDENTIFY, new TrackingEvent($customer));

// now track a `click` event
$dispatcher->dispatch(TrackingEvent::ACTION, new ActionEvent($customer, 'click'));

```


### Webhooks


The bundle comes with a controller which can consume customer.io [webhooks](http://customer.io/docs/webhooks.html).

To use them, register the routing.xml:

```yml
# app/config/routing.yml

customerio_hooks:
    resource: "@DubtureCustomerIOBundle/Resources/config/routing.xml"

```

Now your hook url will be `http://your.project.com//__dubture/customerio` which you
need to configure over at customer.io.

After doing so, you can listen to webhook events:


```xml

<service id="acme.webhooklistener" class="Acme\DemoBundle\Listener\WebhookListener">
    <tag name="kernel.event_listener" event="customerio.email_clicked" method="onClick" />
</service>

```

```php

use Dubture\CustomerIOBundle\Event\WebHookEvent;

class WebhookListener
{
    public function onClick(WebHookEvent $event)
    {
        $this->logger->info('Customer clicked on email with address: '
        . $event->getEmail());
    }
}

```
