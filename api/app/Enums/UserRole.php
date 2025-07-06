<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case AUTHOR = 'author';
    case MEMBER = 'member';

    /**
     * Get all role values as an array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get role display name
     */
    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'Administrator',
            self::AUTHOR => 'Author',
            self::MEMBER => 'Member',
        };
    }
} 