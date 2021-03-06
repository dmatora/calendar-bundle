<?php

namespace BladeTester\CalendarBundle\Tests\Model;

use BladeTester\CalendarBundle\Model\Calendar,
    BladeTester\CalendarBundle\Model\EventInterface,
    BladeTester\CalendarBundle\Model\EventCategory,
    BladeTester\CalendarBundle\Factory\EventFactory;

class FakeEvent implements EventInterface {
    public function getTitle() {}

    public function setTitle($title) {}

    public function getDescription() {}

    public function setDescription($description) {}

    public function getStart() {}

    public function setStart(\DateTime $start) {}

    public function getEnd() {}

    public function setEnd(\DateTime $end) {}
}



class CalendarTest extends \PHPUnit_Framework_TestCase {

    private $dispatcher;
    private $calendar;
    private $eventRepository;
    private $settingRepository;

    public function setUp() {
        $this->eventRepository = $this->getMock('BladeTester\CalendarBundle\Repository\EventRepositoryInterface');
        $this->settingRepository = $this->getMock('BladeTester\CalendarBundle\Repository\SettingRepositoryInterface');
        $this->dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $this->calendar = new Calendar(
            $this->dispatcher,
            new EventFactory('BladeTester\CalendarBundle\Tests\Model\FakeEvent'),
            $this->eventRepository,
            $this->settingRepository
        );
    }

    /**
     * @test
     */
    public function itBringsSettings() {
        // Arrange

        // Expect
        $this->settingRepository->expects($this->once())
            ->method('getSettings');

        // Act
        $settings = $this->calendar->getSettings();
    }

    /**
     * @test
     */
    public function itUpdatesSettings() {
        // Arrange
        $settings = new \BladeTester\CalendarBundle\Model\Settings;

        // Expect
        $this->settingRepository->expects($this->once())
            ->method('updateSettings');

        // Act
        $settings = $this->calendar->updateSettings($settings);
    }

    /**
     * @test
     */
    public function itCreatesEvents() {
        // Arrange

        // Act
        $event = $this->calendar->createEvent();

        // Assert
        $this->assertEquals('BladeTester\CalendarBundle\Tests\Model\FakeEvent', get_class($event));
    }


    /**
     * @test
     */
    public function itBringsAnEventById() {
        // Arrange
        $event_id = 34;

        // Expect
        $this->eventRepository->expects($this->once())
            ->method('find')
            ->with(34);

        // Act
        $this->calendar->find($event_id);
    }


    /**
     * @test
     */
    public function itBringsAllEvents() {
        // Arrange

        // Expect
        $this->eventRepository->expects($this->once())
            ->method('findAll');

        // Act
        $this->calendar->findAll();
    }


    /**
     * @test
     */
    public function itBringsNextEvents() {
        // Arrange

        // Expect
        $this->eventRepository->expects($this->once())
            ->method('findNext');

        // Act
        $this->calendar->findNext();
    }


    /**
     * @test
     */
    public function itBringsEventsBetweenDates() {
        // Arrange
        $start = new \DateTime('2013-02-02');
        $end = new \DateTime('2013-05-21');

        // Expect
        $this->eventRepository->expects($this->once())
            ->method('findBetween')
            ->with($start, $end);

        // Act
        $this->calendar->findBetween($start, $end);
    }

    /**
     * @test
     */
    public function itBringsEventsByDay() {
        // Arrange
        $today = new \DateTime();

        // Expect
        $this->eventRepository->expects($this->once())
            ->method('findAllByDay')
            ->with($today);

        // Act
        $this->calendar->findAllByDay($today);
    }

    /**
     * @test
     */
    public function itBringsEventsByWeek() {
        // Arrange
        $today = new \DateTime();

        // Expect
        $this->eventRepository->expects($this->once())
            ->method('findAllByWeek')
            ->with($today);

        // Act
        $this->calendar->findAllByWeek($today);
    }


    /**
     * @test
     */
    public function itBringsEventsByMonth() {
        // Arrange
        $today = new \DateTime();

        // Expect
        $this->eventRepository->expects($this->once())
            ->method('findAllByMonth')
            ->with($today);

        // Act
        $this->calendar->findAllByMonth($today);
    }

    /**
     * @test
     */
    public function itPersistsEvents() {
        // Arrange
        $event = $this->calendar->createEvent();

        // Expect
        $this->eventRepository->expects($this->once())
            ->method('persist')
            ->with($event);

        // Act
        $this->calendar->persist($event);
    }


    /**
     * @test
     */
    public function itDispatchesAnEventBeforePersisting() {
        // Arrange
        $event = $this->calendar->createEvent();

        // Expect
        $this->dispatcher->expects($this->at(0))
            ->method('dispatch')
            ->with('calendar.pre-persist', $this->isInstanceOf('BladeTester\CalendarBundle\Event\CalendarEvent'));

        // Act
        $this->calendar->persist($event);
    }

    /**
     * @test
     */
    public function itDispatchesAnEventAfterPersisting() {
        // Arrange
        $event = $this->calendar->createEvent();

        // Expect
        $this->dispatcher->expects($this->at(1))
            ->method('dispatch')
            ->with('calendar.post-add', $this->isInstanceOf('BladeTester\CalendarBundle\Event\CalendarEvent'));

        // Act
        $this->calendar->persist($event);
    }

    /**
     * @test
     */
    public function itDispatchesAnEventAfterUpdating() {
        // Arrange
        $event = $this->calendar->createEvent();

        // Expect
        $this->dispatcher->expects($this->once())
            ->method('dispatch')
            ->with('calendar.post-update', $this->isInstanceOf('BladeTester\CalendarBundle\Event\CalendarEvent'));

        // Act
        $this->calendar->update($event);
    }

    /**
     * @test
     */
    public function itBringsAllDaysInAWeekSheet() {
        // Arrange

        // Act
        $days = $this->calendar->getWeekSheetDays(new \DateTime('2013-03-13'));

        // Expect
        $this->assertCount(7, $days);
        $this->assertEquals('2013-03-11', $days[0]->format('Y-m-d'));
        $this->assertEquals('2013-03-17', $days[6]->format('Y-m-d'));

    }


    /**
     * @test
     */
    public function itBringsAllDaysInAMonthSheet() {
        // Arrange

        // Act
        $days = $this->calendar->getMonthSheetDays(new \DateTime('2013-03-13'));

        // Expect
        $this->assertCount(35, $days);
        $this->assertEquals('2013-02-25', $days[0]->format('Y-m-d'));
        $this->assertEquals('2013-03-31', $days[34]->format('Y-m-d'));

    }

    private function mockEvent(array $data = array()) {
        $event = $this->getMock('BladeTester\CalendarBundle\Model\EventInterface');
        if (isset($data['start'])) {
            $event->expects($this->any())
                ->method('getStart')
                ->will($this->returnValue($data['start']));
        }
        return $event;
    }

}
