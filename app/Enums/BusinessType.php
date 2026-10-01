<?php

namespace App\Enums;

enum BusinessType: string
{
    case GymFitness = 'gym_fitness';
    case TrainingCentre = 'training_centre';
    case Coworking = 'coworking';
    case SportsClub = 'sports_club';
    case Cleaning = 'cleaning';
    case Security = 'security';
    case Maintenance = 'maintenance';
    case ItSupport = 'it_support';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::GymFitness => 'Gym & fitness studio',
            self::TrainingCentre => 'Training centre',
            self::Coworking => 'Coworking space',
            self::SportsClub => 'Sports club or pool',
            self::Cleaning => 'Cleaning services',
            self::Security => 'Security services',
            self::Maintenance => 'Maintenance services',
            self::ItSupport => 'IT support',
            self::Other => 'Other recurring business',
        };
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $type): array => ['value' => $type->value, 'label' => $type->label()],
            self::cases(),
        );
    }
}
