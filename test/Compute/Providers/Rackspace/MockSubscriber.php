<?php

namespace LibCloud\Test\Compute\Providers\Rackspace;

use Guzzle\Common\Event;
use Guzzle\Plugin\Mock\MockPlugin;

class MockSubscriber extends MockPlugin
{
    public static function getSubscribedEvents()
    {
        return array(
            'request.before_send' => array(
                array('onRequestBeforeSend', -999)
            )
        );
    }

    public function onRequestBeforeSend(Event $event)
    {
        parent::onRequestBeforeSend($event);
        $event->stopPropagation();
    }
}
