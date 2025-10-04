<?php

use App\Helpers\ReadingTimeHelper;

const TEST_WORD = 'word ';

describe('ReadingTimeHelper Integration Tests', function (): void {
    it('calculates and formats short article', function (): void {
        $content = str_repeat(TEST_WORD, 50);
        $minutes = ReadingTimeHelper::calculate($content);
        $formatted = ReadingTimeHelper::format($minutes);

        expect($minutes)->toBe(1)
            ->and($formatted)->toBe('1 minuto');
    });

    it('calculates and formats medium article', function (): void {
        $content = str_repeat(TEST_WORD, 800);
        $minutes = ReadingTimeHelper::calculate($content);
        $formatted = ReadingTimeHelper::format($minutes);

        expect($minutes)->toBe(4)
            ->and($formatted)->toBe('4 minutos');
    });

    it('calculates and formats long article', function (): void {
        $content = str_repeat(TEST_WORD, 12000);
        $minutes = ReadingTimeHelper::calculate($content);
        $formatted = ReadingTimeHelper::format($minutes);

        expect($minutes)->toBe(60)
            ->and($formatted)->toBe('1 hora');
    });

    it('handles real-world content', function (): void {
        $content = '
            <article>
                <h1>Como Melhorar Sua Produtividade</h1>
                <p>A produtividade é um dos fatores mais importantes para o sucesso profissional.</p>
                <p>Existem diversas técnicas que podem ajudar você a ser mais produtivo no seu dia a dia.</p>
                <ul>
                    <li>Técnica Pomodoro</li>
                    <li>Getting Things Done</li>
                    <li>Time Blocking</li>
                </ul>
                <p>Cada uma dessas técnicas tem suas vantagens e pode ser adaptada para diferentes tipos de trabalho.</p>
            </article>
        ';

        $minutes = ReadingTimeHelper::calculate($content);
        $formatted = ReadingTimeHelper::format($minutes);

        expect($minutes)->toBe(1)
            ->and($formatted)->toBe('1 minuto');
    });
});
