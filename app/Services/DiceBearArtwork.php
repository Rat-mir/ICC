<?php

namespace App\Services;

use DiceBear\Avatar;
use DiceBear\Style;

final class DiceBearArtwork
{
    public const STYLES = [
        'adventurer' => ['Adventurer', 'Мультяшный портрет с очками, серьгами и разными выражениями лица.'],
        'notionists' => ['Notionists', 'Рисованный персонаж по пояс. Можно менять одежду и жесты.'],
        'pixel-art' => ['Pixel Art', 'Пиксельные персонажи с разными причёсками, одеждой и аксессуарами.'],
        'critters' => ['Critters', 'Цветные существа с рогами, ушами и узорами.'],
        'lorelei' => ['Lorelei', 'Портрет тонкими линиями с разными причёсками и аксессуарами.'],
    ];

    public const ARCHIVED_STYLES = ['notionists'];

    public const REVIEW_STYLES = [
        'adventurer-neutral', 'avataaars', 'avataaars-neutral', 'big-ears',
        'big-ears-neutral', 'big-smile', 'bottts', 'bottts-neutral',
        'cameo', 'clay', 'croodles', 'croodles-neutral',
        'cutouts', 'dylan', 'fun-emoji', 'gaze',
        'line-face', 'lorelei-neutral', 'marbles', 'micah',
        'miniavs', 'moods', 'notionists-neutral', 'open-peeps',
        'personas', 'pixel-art-neutral', 'pixelbot', 'shadows',
        'sprouts', 'thumbs', 'toon-head', 'voxel-art',
        'voxel-bot',
    ];

    private const LABELS = [
        'eyes' => 'Глаза', 'eyebrows' => 'Брови', 'brows' => 'Брови', 'mouth' => 'Рот',
        'nose' => 'Нос', 'head' => 'Голова', 'body' => 'Основа', 'skin' => 'Тон кожи',
        'hair' => 'Причёска', 'frontHair' => 'Чёлка', 'rearHair' => 'Задние волосы',
        'facialHair' => 'Борода и усы', 'beard' => 'Борода', 'mustache' => 'Усы',
        'glasses' => 'Очки', 'accessories' => 'Аксессуары', 'earrings' => 'Серьги',
        'ears' => 'Уши', 'clothes' => 'Одежда', 'clothing' => 'Одежда', 'top' => 'Верхняя часть',
        'outfit' => 'Наряд', 'cheeks' => 'Щёки', 'pattern' => 'Узор', 'hat' => 'Головной убор',
    ];

    public static function supports(?string $style): bool
    {
        return isset(self::STYLES[$style]) || in_array($style, self::REVIEW_STYLES, true);
    }

    public static function metadata(string $style): array
    {
        return self::definition($style)['meta'];
    }

    private static function parts(string $style): array
    {
        if (isset(self::PARTS[$style])) {
            return self::PARTS[$style];
        }

        static $parts = [];
        if (isset($parts[$style])) {
            return $parts[$style];
        }
        $definition = self::definition($style);
        $colors = array_filter($definition['colors'], static fn (array $group) =>
            !empty($group['values']) && !preg_grep('/^(?!#[0-9a-f]{6}$)/i', $group['values'])
        );
        $aliases = ['head' => 'skin', 'clothes' => 'clothing'];
        $options = [];
        foreach ($definition['components'] as $name => $component) {
            if ($name === 'animation') {
                continue;
            }
            $color = isset($colors[$name]) ? $name : ($aliases[$name] ?? null);
            $color = isset($colors[$color]) ? $color : null;
            $label = self::LABELS[$name] ?? ucfirst(preg_replace('/([a-z])([A-Z])/', '$1 $2', $name));
            $options[$name] = [$name, $color, $label];
            unset($colors[$color]);
        }
        unset($colors['background']);
        foreach ($colors as $name => $color) {
            if (count($color['values']) > 1) {
                $label = self::LABELS[$name] ?? ucfirst(preg_replace('/([a-z])([A-Z])/', '$1 $2', $name));
                $options[$name.'Color'] = [null, $name, $label.' · цвет'];
            }
        }

        return $parts[$style] = $options;
    }

    private const PARTS = [
        'adventurer' => [
            'body' => ['head', 'skin', 'Тон кожи'], 'hair' => ['hair', 'hair', 'Причёска'],
            'face' => ['eyes', null, 'Глаза'], 'eyebrows' => ['eyebrows', null, 'Брови'],
            'mouth' => ['mouth', null, 'Рот'], 'accessory' => ['glasses', null, 'Очки'],
            'earrings' => ['earrings', null, 'Серьги'], 'details' => ['details', null, 'Детали лица'],
        ],
        'notionists' => [
            'body' => ['head', null, 'Основа'], 'clothes' => ['clothes', null, 'Одежда'],
            'hair' => ['hair', null, 'Причёска'], 'face' => ['eyes', null, 'Глаза'],
            'mouth' => ['mouth', null, 'Рот'], 'nose' => ['nose', null, 'Нос'],
            'gesture' => ['gesture', null, 'Жест'], 'accessory' => ['glasses', null, 'Очки'],
            'eyebrows' => ['eyebrows', null, 'Брови'],
        ],
        'pixel-art' => [
            'body' => [null, 'skin', 'Тон кожи'], 'hair' => ['hair', 'hair', 'Причёска'],
            'clothes' => ['clothes', 'clothing', 'Одежда'], 'face' => ['eyes', 'eyes', 'Глаза'],
            'mouth' => ['mouth', 'mouth', 'Рот'], 'accessory' => ['accessories', 'accessories', 'Украшения'],
            'glasses' => ['glasses', 'glasses', 'Очки'], 'hat' => ['hat', 'hat', 'Головной убор'],
            'beard' => ['beard', null, 'Борода'],
        ],
        'critters' => [
            'body' => ['body', 'body', 'Форма'], 'hair' => ['top', 'accent', 'Уши и рога'],
            'face' => ['eyes', null, 'Глаза'], 'mouth' => ['mouth', null, 'Рот'],
            'pattern' => ['pattern', null, 'Узор'], 'details' => ['cheeks', null, 'Щёки'],
        ],
        'lorelei' => [
            'body' => ['head', 'skin', 'Лицо'], 'hair' => ['hair', 'hair', 'Причёска'],
            'face' => ['eyes', null, 'Глаза'], 'mouth' => ['mouth', null, 'Рот'],
            'nose' => ['nose', null, 'Нос'], 'accessory' => ['glasses', null, 'Очки'],
            'earrings' => ['earrings', null, 'Серьги'], 'eyebrows' => ['eyebrows', null, 'Брови'],
        ],
    ];

    private const BACKGROUNDS = ['#e7ddf7', '#f5dbe7', '#d2e9eb', '#f5e7c8', '#cfd9f4', '#dbead5'];

    private static function definition(string $style): array
    {
        static $definitions = [];

        return $definitions[$style] ??= json_decode(
            file_get_contents(resource_path("dicebear/$style.json")), true, 512, JSON_THROW_ON_ERROR
        );
    }

    private static function variants(string $style, string $component): array
    {
        $variants = self::definition($style)['components'][$component]['variants'];
        if ($style === 'adventurer' && $component === 'hair') {
            $variants = array_filter($variants, fn (array $variant) => str_contains(json_encode($variant), '"earrings"'));
        }
        $keys = array_keys($variants);
        if (count($keys) <= 16) {
            return $keys;
        }
        return array_map(fn (int $index) => $keys[(int) round($index * (count($keys) - 1) / 15)], range(0, 15));
    }

    public static function options(string $style): array
    {
        static $options = [];
        if (isset($options[$style])) {
            return $options[$style];
        }
        $definition = self::definition($style);
        $parts = ['background' => [['Фон'], self::BACKGROUNDS]];
        foreach (self::parts($style) as $category => [$component, $color, $label]) {
            $variants = $component === null ? ['default'] : self::variants($style, $component);
            $names = count($variants) === 1 ? [$label] : array_map(
                fn (int $index) => $label.' '.($index + 1), array_keys($variants)
            );
            $parts[$category] = [$names, $color ? $definition['colors'][$color]['values'] : ['#000000']];
        }

        return $options[$style] = $parts;
    }

    public static function descriptor(string $path): ?array
    {
        if (!preg_match('~^dicebear/([a-z-]+)/([a-zA-Z]+)/(\d{1,2})/(\d{1,2})$~D', $path, $match)) {
            return null;
        }
        [, $style, $category, $variant, $shade] = $match;
        if (!self::supports($style)) {
            return null;
        }
        $option = self::options($style)[$category] ?? null;
        if ($option === null || !isset($option[0][$variant], $option[1][$shade])) {
            return null;
        }

        return [
            'style' => $style, 'category' => $category, 'variant' => (int) $variant,
            'color' => strtolower($option[1][$shade]), 'back' => false, 'dicebear' => true,
            'required' => in_array($category, self::required($style), true),
            'label' => self::parts($style)[$category][2] ?? 'Фон',
        ];
    }

    public static function required(string $style): array
    {
        return $style === 'lorelei' ? ['body', 'face', 'hair', 'mouth', 'nose', 'eyebrows']
            : match ($style) {
                'notionists' => ['body', 'face', 'hair', 'clothes', 'mouth', 'nose', 'eyebrows'],
                'pixel-art' => ['body', 'face', 'hair', 'clothes', 'mouth'],
                'adventurer' => ['body', 'face', 'hair', 'mouth', 'eyebrows'],
                'critters' => ['body', 'face', 'mouth'],
                default => array_keys(array_filter(self::parts($style), static fn (array $part) =>
                    $part[0] !== null && (self::definition($style)['components'][$part[0]]['probability'] ?? 100) === 100
                )),
            };
    }

    public static function svg(string $style, array $parts): string
    {
        $definition = self::definition($style);
        $selected = array_column($parts, null, 'category');
        $options = ['seed' => 'icc', 'size' => 512];
        foreach ($definition['components'] as $name => $component) {
            if (isset($component['probability'])) {
                $options[$name.'Probability'] = 0;
            }
        }
        foreach (self::parts($style) as $category => [$component, $color]) {
            $part = $selected[$category] ?? null;
            if (!$part && !in_array($category, self::required($style), true)) {
                if ($component !== null) {
                    $options[$component.'Probability'] = 0;
                }
                continue;
            }
            if ($component !== null) {
                $variants = self::variants($style, $component);
                $options[$component.'Variant'] = $variants[$part['variant'] ?? 0];
                $options[$component.'Probability'] = 100;
            }
            if ($color && $part) {
                $options[$color.'Color'] = ltrim($part['color'], '#');
            }
        }
        if (isset($definition['components']['animation']['variants']['none'])) {
            $options['animationVariant'] = 'none';
        }
        if (isset($selected['background'])) {
            $options['backgroundColor'] = ltrim($selected['background']['color'], '#');
        }

        static $styles = [];
        $engineStyle = $styles[$style] ??= new Style($definition);
        return (string) new Avatar($engineStyle, $options);
    }
}
