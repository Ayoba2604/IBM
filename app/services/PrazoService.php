<?php
declare(strict_types=1);

class PrazoService
{
    public const DIAS = 15;

    public static function deadline(string $baseDate): string
    {
        $parsed = DateTimeImmutable::createFromFormat("!Y-m-d", $baseDate);
        if (!$parsed || $parsed->format("Y-m-d") !== $baseDate) {
            throw new InvalidArgumentException("Data-base inválida para o prazo.");
        }
        return $parsed->modify("+" . self::DIAS . " days")->format("Y-m-d");
    }

    public static function daysRemaining(string $deadline): int
    {
        return (int) (new DateTimeImmutable("today"))
            ->diff(new DateTimeImmutable($deadline))
            ->format("%r%a");
    }

    public static function isOverdue(string $deadline): bool
    {
        return self::daysRemaining($deadline) < 0;
    }
}
