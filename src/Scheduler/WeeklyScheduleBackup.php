<?php

namespace App\Scheduler;

use App\Scheduler\Message\WeeklyDatabaseBackup;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

#[AsSchedule]
class WeeklyScheduleBackup implements ScheduleProviderInterface
{
    public function getSchedule(): Schedule
    {
        return (new Schedule())->add(RecurringMessage::every('10 seconds', new WeeklyDatabaseBackup()));
    }
}