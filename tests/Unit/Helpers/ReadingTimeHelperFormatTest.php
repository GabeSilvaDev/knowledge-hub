<?php

use App\Helpers\ReadingTimeHelper;

describe('ReadingTimeHelper Format Method', function (): void {
    it('formats zero minutes', function (): void {
        expect(ReadingTimeHelper::format(0))->toBe('Menos de 1 minuto');
    });

    it('formats negative minutes', function (): void {
        expect(ReadingTimeHelper::format(-5))->toBe('Menos de 1 minuto');
    });

    it('formats 1 minute', function (): void {
        expect(ReadingTimeHelper::format(1))->toBe('1 minuto');
    });

    it('formats multiple minutes under an hour', function (): void {
        expect(ReadingTimeHelper::format(2))->toBe('2 minutos')
            ->and(ReadingTimeHelper::format(30))->toBe('30 minutos')
            ->and(ReadingTimeHelper::format(59))->toBe('59 minutos');
    });

    it('formats exactly 1 hour', function (): void {
        expect(ReadingTimeHelper::format(60))->toBe('1 hora');
    });

    it('formats multiple hours without minutes', function (): void {
        expect(ReadingTimeHelper::format(120))->toBe('2 horas')
            ->and(ReadingTimeHelper::format(180))->toBe('3 horas');
    });

    it('formats hours with minutes', function (): void {
        expect(ReadingTimeHelper::format(65))->toBe('1h 5min')
            ->and(ReadingTimeHelper::format(90))->toBe('1h 30min')
            ->and(ReadingTimeHelper::format(125))->toBe('2h 5min')
            ->and(ReadingTimeHelper::format(150))->toBe('2h 30min');
    });

    it('formats large reading times', function (): void {
        expect(ReadingTimeHelper::format(300))->toBe('5 horas')
            ->and(ReadingTimeHelper::format(367))->toBe('6h 7min');
    });
});
