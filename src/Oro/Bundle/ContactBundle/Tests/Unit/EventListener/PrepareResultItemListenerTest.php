<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\EventListener;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\SearchBundle\Event\PrepareResultItemEvent;
use Oro\Bundle\SearchBundle\Query\Result\Item;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\ContactPhone;
use Oro\Bundle\ContactBundle\Formatter\ContactNameFormatter;
use Oro\Bundle\ContactBundle\EventListener\PrepareResultItemListener;

class PrepareResultItemListenerTest extends \PHPUnit_Framework_TestCase
{
    /** @var ContactNameFormatter */
    protected $nameFormatter;

    public function setUp()
    {
        $this->nameFormatter = $this->getMockBuilder('Oro\Bundle\ContactBundle\Formatter\ContactNameFormatter')
            ->disableOriginalConstructor()
            ->getMock();

        $this->nameFormatter->expects($this->any())
            ->method('format')
            ->will($this->returnCallback(function (Contact $contact) {
                return trim(implode(' ', [$contact->getFirstName(), $contact->getLastName()]));
            }));
    }

    /**
     * @dataProvider prepareEmailItemDataEventProvider
     */
    public function testPrepareEmailItemDataEvent($event, $expectedEvent)
    {
        $listener = new PrepareResultItemListener($this->nameFormatter);
        $listener->prepareEmailItemDataEvent($event);

        $this->assertEquals($expectedEvent, $event);
    }

    public function prepareEmailItemDataEventProvider()
    {
        return [
            'event with contact without title' => [
                new PrepareResultItemEvent(
                    new Item(
                        $this->getObjectManager(
                            (new Contact())
                                ->setFirstName('first')
                        ),
                        'Oro\Bundle\ContactBundle\Entity\Contact',
                        1
                    )
                ),
                new PrepareResultItemEvent(
                    new Item(
                        $this->getObjectManager(
                            (new Contact())
                                ->setFirstName('first')
                        ),
                        'Oro\Bundle\ContactBundle\Entity\Contact',
                        1,
                        'first'
                    )
                ),
            ],
            'event with contact with title' => [
                new PrepareResultItemEvent(
                    new Item(
                        $this->getObjectManager(
                            (new Contact())
                                ->setFirstName('first')
                        ),
                        'Oro\Bundle\ContactBundle\Entity\Contact',
                        1,
                        'preset title'
                    )
                ),
                new PrepareResultItemEvent(
                    new Item(
                        $this->getObjectManager(
                            (new Contact())
                                ->setFirstName('first')
                        ),
                        'Oro\Bundle\ContactBundle\Entity\Contact',
                        1,
                        'preset title'
                    )
                ),
            ],
            'event without contact without title' => [
                new PrepareResultItemEvent(
                    new Item(
                        $this->getObjectManager(
                            (new ContactPhone())
                                ->setPhone('53582379475')
                        ),
                        'Oro\Bundle\ContactBundle\Entity\ContactPhone',
                        1
                    )
                ),
                new PrepareResultItemEvent(
                    new Item(
                        $this->getObjectManager(
                            (new ContactPhone())
                                ->setPhone('53582379475')
                        ),
                        'Oro\Bundle\ContactBundle\Entity\ContactPhone',
                        1
                    )
                ),
            ],
            'event without contact with title' => [
                new PrepareResultItemEvent(
                    new Item(
                        $this->getObjectManager(
                            (new ContactPhone())
                                ->setPhone('53582379475')
                        ),
                        'Oro\Bundle\ContactBundle\Entity\ContactPhone',
                        1,
                        'preset title'
                    )
                ),
                new PrepareResultItemEvent(
                    new Item(
                        $this->getObjectManager(
                            (new ContactPhone())
                                ->setPhone('53582379475')
                        ),
                        'Oro\Bundle\ContactBundle\Entity\ContactPhone',
                        1,
                        'preset title'
                    )
                ),
            ],
        ];
    }
    

    /**
     * @param object $entity
     *
     * @return ObjectManager
     */
    protected function getObjectManager($entity)
    {
        $repository = $this->createMock('Doctrine\Common\Persistence\ObjectRepository');
        $repository
            ->expects($this->any())
            ->method('find')
            ->will($this->returnValue($entity));

        $om = $this->createMock('Doctrine\Common\Persistence\ObjectManager');
        $om->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($repository));

        return $om;
    }
}
