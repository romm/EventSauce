<?php

declare(strict_types=1);

namespace EventSauce\EventSourcing;

use DateTimeImmutable;
use EventSauce\EventSourcing\Time\TestClock;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Throwable;

class MessageTest extends TestCase
{
    /**
     * @test
     */
    public function accessors(): void
    {
        $event = EventStub::create('some value');
        $initialHeaders = ['initial' => 'header value'];
        $message = new Message($event, $initialHeaders);
        $this->assertSame($event, $message->event());
        $this->assertEquals($initialHeaders, $message->headers());
    }

    /**
     * @test
     */
    public function accessing_the_version_when_not_set(): void
    {
        $this->expectException(RuntimeException::class);
        (new Message(EventStub::create('v')))->aggregateVersion();
    }

    /**
     * @test
     */
    public function aggregate_root_id_accessor(): void
    {
        $event = EventStub::create('some value');
        $message = new Message($event);
        $this->assertNull($message->aggregateRootId());
        $message = $message->withHeader(Header::AGGREGATE_ROOT_ID, DummyAggregateRootId::generate());
        $this->assertInstanceOf(AggregateRootId::class, $message->aggregateRootId());
    }

    /**
     * @test
     */
    public function time_of_recording_accessor(): void
    {
        $event = EventStub::create('some value');
        $message = new Message($event);
        $timeOfRecording = (new TestClock())->now();
        $message = $message->withHeader(Header::TIME_OF_RECORDING, $timeOfRecording->format('Y-m-d H:i:s.uO'));
        $this->assertInstanceOf(DateTimeImmutable::class, $message->timeOfRecording());
        $this->assertSame($timeOfRecording->format('Y-m-d H:i:s.uO'), $message->timeOfRecording()->format('Y-m-d H:i:s.uO'));
    }

    /**
     * @test
     */
    public function time_of_recording_is_asserted(): void
    {
        $message = new Message(EventStub::create('this'));

        $this->expectException(Throwable::class);

        $message->timeOfRecording();
    }
}
