<?php
declare(strict_types=1);
class PrazoService
{
    public static function deadline(string $baseDate): string
    {
        return (new DateTimeImmutable($baseDate))
            ->modify("+15 days")
            ->format("Y-m-d");
    }
    public static function daysRemaining(string $deadline): int
    {
        return (int) (new DateTimeImmutable("today"))
            ->diff(new DateTimeImmutable($deadline))
            ->format("%r%a");
    }
}
