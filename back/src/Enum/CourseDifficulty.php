<?php

namespace App\Enum;

enum CourseDifficulty: string
{
    case EASY = 'easy';
    case INTERMEDIATE = 'intermediate';
    case ADVANCED = 'advanced';
    case EXPERT = 'expert';

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function getLabels(): array
    {
        return [
            self::EASY->value => 'Facile',
            self::INTERMEDIATE->value => 'Intermédiaire',
            self::ADVANCED->value => 'Avancé',
            self::EXPERT->value => 'Expert',
        ];
    }
} 