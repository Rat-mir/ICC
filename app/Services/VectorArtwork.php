<?php

namespace App\Services;

final class VectorArtwork
{
    public const STYLES = [
        'chibi' => ['Чиби', 'Персонаж в полный рост. Подберите причёску, наряд и выражение лица.', '#f2cadf', '#a35b87'],
        'anime' => ['Аниме', 'Портрет в три четверти с рукой у лица.', '#d7d0f1', '#7060a1'],
        'furry' => ['Фурри', 'Шесть видов животных с разными рисунками шерсти.', '#f5dbad', '#ab613e'],
        'pony' => ['Пони', 'Соберите пони, единорога, пегаса или аликорна.', '#b9dfeb', '#9a63c2'],
    ];

    private const LEGACY_PORTRAIT = ['Базовый портрет', '', '#c5e3d9', '#456f68'];

    public static function options(string $style): array
    {
        $definition = self::STYLES[$style] ?? self::LEGACY_PORTRAIT;
        $options = [
            'background' => [['Орнамент', 'Однотонный'], [$definition[2], '#d5c9ed', '#c6e2df']],
            'body' => [[$style === 'furry' ? 'Мех' : 'Тон кожи'], $style === 'furry'
                ? ['#c97945', '#926fb3', '#a1a3b4', '#e9cfad']
                : ['#f4c5a8', '#ce916f', '#a6684b', '#734636']],
            'clothes' => [['Куртка', 'Худи', 'Рубашка'], [$definition[3], '#35415e', '#ab5d70', '#d5a464']],
            'face' => [['Открытые', 'Прищур', 'Круглые'], ['#7a589e', '#4e807d', '#946438', '#5879aa']],
            'hair' => [['Чёлка', 'Каре', 'Высокий хвост'], ['#493549', '#944932', '#d3a66d', '#8280bf', '#d9d6e5']],
            'accessory' => [['Очки', 'Звезда', 'Серьги'], ['#bc8354', '#6e579a']],
        ];
        if ($style === 'portrait') {
            return $options;
        }

        $names = match ($style) {
            'chibi' => [
                'clothes' => ['Платье с воротником', 'Худи', 'Комбинезон', 'Матроска', 'Пальто', 'Платье со звездой', 'Спортивный костюм'],
                'hair' => ['Длинные с чёлкой', 'Каре', 'Высокий хвост', 'Два хвоста', 'Кудри', 'Короткая стрижка', 'Косички'],
                'accessory' => ['Очки', 'Звёздные заколки', 'Серьги', 'Бант', 'Наушники', 'Сумка'],
            ],
            'anime' => [
                'clothes' => ['Футболка', 'Толстовка', 'Блуза', 'Пиджак', 'Форма с бантом', 'Водолазка', 'Платье'],
                'hair' => ['Длинные прямые', 'Удлинённое каре', 'Боковой хвост', 'Волны', 'Короткие пряди', 'Мягкие кудри', 'Коса'],
                'accessory' => ['Тонкая оправа', 'Заколки', 'Подвеска в ухе', 'Чокер', 'Бант для волос', 'Кулон'],
            ],
            'furry' => [
                'body' => self::furryCoats(),
                'clothes' => ['Жакет', 'Худи', 'Плащ', 'Жилет', 'Перевязь', 'Пальто с шарфом'],
                'hair' => ['Мягкий хохолок', 'Боковая прядь', 'Высокий хохолок', 'Завитки', 'Взъерошенные', 'Короткие'],
                'accessory' => ['Очки', 'Звёзды', 'Серьги', 'Ошейник с жетоном', 'Полосы на морде', 'Шейный бант'],
            ],
            'pony' => [
                'body' => ['Масть'],
                'clothes' => ['Шарф', 'Попона', 'Колокольчик', 'Украшения на копыта', 'Бант', 'Накидка'],
                'face' => ['Большие глаза', 'Мягкий взгляд', 'Распахнутые глаза', 'Улыбка', 'Удивление'],
                'hair' => ['Мягкие волны', 'Прямые пряди', 'Кудри', 'Коса', 'Взъерошенная грива', 'Длинный хвост'],
                'accessory' => ['Рог единорога', 'Крылья пегаса', 'Рог и крылья', 'Диадема'],
            ],
        };
        foreach ($names as $category => $variants) {
            $options[$category][0] = $variants;
        }
        $options['background'][0] = [$style === 'pony' ? 'Облака' : 'Орнамент', 'Однотонный', 'Шахматный узор'];
        if ($style !== 'pony') {
            $options['face'][0] = ['Открытые', 'Прищур', 'Круглые', $style === 'furry' ? 'Хитрый взгляд' : 'Улыбка', 'Удивление'];
        }
        $extraColors = [
            'background' => ['#a8bce5', '#f4d2ae', '#9b87c3', '#e3a9b7'],
            'body' => $style === 'furry' ? ['#594b58', '#ba8676', '#ddb8cf', '#6d9a98', '#ceae69', '#ece6e1'] : ['#f9ddca', '#e7aa92', '#885040', '#59372f'],
            'clothes' => ['#8274bd', '#649898', '#c77e9f', '#efc9aa', '#f2e9ed', '#51435d'],
            'face' => ['#a94c74', '#a48b42', '#434256', '#78a863', '#5da5b3', '#d98158'],
            'hair' => ['#634453', '#392f43', '#d98eae', '#72a6a5', '#c98861', '#dbb7e8'],
            'accessory' => ['#df8fa6', '#c2afea', '#8ebfc6', '#ead38b', '#f3e8e7'],
        ];
        if ($style === 'pony') {
            $options['body'][1] = ['#e6a8d3', '#a5cdeb', '#b9a5e7', '#a1d8c7', '#f0c989', '#eaa48e', '#d6de92', '#f4e2ec', '#9b90c4', '#e8b8a9'];
            $extraColors['body'] = [];
            $options['hair'][1] = ['#7652a8', '#d28cae', '#e4b973', '#8280bf', '#d9d6e5'];
        }
        foreach ($extraColors as $category => $colors) {
            $options[$category][1] = [...$options[$category][1], ...$colors];
        }
        return $options;
    }

    private static function furryCoats(): array
    {
        $species = ['Лиса', 'Кошка', 'Волк', 'Кролик', 'Медведь', 'Енот'];
        return [...$species, ...array_map(fn ($name) => $name.' · полосы', $species),
            ...array_map(fn ($name) => $name.' · пятна', $species)];
    }

    public static function descriptor(string $path): ?array
    {
        if (str_starts_with($path, 'dicebear/')) {
            return DiceBearArtwork::descriptor($path);
        }
        if (!preg_match('~^vector/(chibi|anime|furry|portrait|pony)/(background|body|clothes|face|hair|accessory)/(0|[1-9][0-9]?)/(0|[1-9][0-9]?)$~D', $path, $parts)) {
            return null;
        }

        [, $style, $category, $variant, $shade] = $parts;
        [$names, $colors] = self::options($style)[$category];
        if (!isset($names[$variant], $colors[$shade])) {
            return null;
        }

        $layer = ['style' => $style, 'category' => $category, 'variant' => (int) $variant, 'color' => $colors[$shade],
            'back' => $category === 'hair' && in_array($style, ['chibi', 'anime', 'pony'], true)];
        if ($style === 'furry' && $category === 'body') {
            $layer['species'] = (int) $variant % 6;
            $layer['coat'] = intdiv((int) $variant, 6);
        }
        return $layer;
    }

    public static function svg(array $layer, bool $back = false): string
    {
        ['style' => $style, 'category' => $category, 'variant' => $variant, 'color' => $color] = $layer;
        if ($style !== 'portrait') {
            return (new MakerIllustration($style, $variant, $color))->render($category, $back);
        }
        $ink = '#36283e';
        $chibi = $style === 'chibi';
        $furry = $style === 'furry';
        $portrait = $style === 'portrait';
        $head = $chibi ? 'M87 208Q75 94 184 69Q258 47 333 81Q437 115 422 220Q421 313 328 342Q254 369 178 339Q85 309 87 208Z'
            : ($furry ? 'M112 172Q75 129 80 51Q146 63 186 115Q254 87 328 115Q375 63 435 51Q442 128 396 174L420 242L391 234L405 268Q349 306 288 329Q256 344 224 329Q157 307 105 268L122 233L91 243Z'
            : ($portrait ? 'M134 184Q128 82 253 78Q376 79 380 183L369 253Q347 324 256 342Q170 324 144 251Z'
            : 'M119 180Q117 74 253 71Q389 73 392 176L373 258Q341 311 256 347Q181 315 143 260Z'));

        $body = '<path d="M211 304L208 363Q168 377 118 399Q68 425 49 512H463Q444 425 394 399Q344 377 304 363L301 304Z" fill="'.$color.'"/><path d="'.$head.'" fill="'.$color.'"/>';
        $body .= $furry
            ? '<path d="M100 80Q141 97 163 130L121 151ZM412 80L391 151L351 130Z" fill="#e8b2a9"/><path d="M114 242Q167 217 256 267Q347 218 398 242L391 264Q329 311 256 329Q183 312 121 266Z" fill="#f7e4ca" stroke="none"/>'
            : '<path d="M144 242Q171 227 199 246M313 246Q344 228 369 241" fill="none" stroke="#af5d67" opacity=".32" stroke-width="12"/>';

        $garment = [
            'M209 360Q181 377 116 393Q67 420 48 512H464Q445 420 396 393Q331 377 303 360L256 402Z',
            'M208 350Q162 355 145 387Q70 404 48 512H464Q442 404 367 387Q350 355 304 350Q256 404 208 350Z',
            'M209 360Q170 377 112 396Q68 422 49 512H463Q443 422 400 396Q342 377 303 360L256 395Z',
        ][$variant % 3];
        $details = [
            '<path d="M200 370L176 410L218 408L202 437L256 507L310 437L294 408L336 410L312 370M256 402V512" fill="none"/><path d="M106 453H157M355 453H406" stroke="#fff" opacity=".55"/>',
            '<path d="M148 389Q255 467 364 389M205 418V464M307 418V464M216 484H296" fill="none"/><circle cx="205" cy="467" r="4" fill="#fff"/><circle cx="307" cy="467" r="4" fill="#fff"/>',
            '<path d="M208 360L172 386L217 431L256 395L295 431L340 386L304 360M256 395V512" fill="#fff" fill-opacity=".24"/><path d="M267 447V449M267 476V478M267 503V505" stroke-width="7"/>',
        ][$variant % 3];

        $eyeWidth = $chibi ? 39 : ($portrait ? 25 : 33);
        $eyeHeight = $variant === 1 ? 12 : ($chibi || $variant === 2 ? 25 : 18);
        $eyes = '';
        foreach ([177, 335] as $x) {
            $eyes .= '<ellipse cx="'.$x.'" cy="219" rx="'.$eyeWidth.'" ry="'.$eyeHeight.'" fill="#fff7f8"/>';
            $eyes .= '<ellipse cx="'.$x.'" cy="220" rx="'.($eyeHeight * .55).'" ry="'.($eyeHeight - 2).'" fill="'.$color.'"/>';
            $eyes .= '<ellipse cx="'.$x.'" cy="220" rx="5" ry="'.max(4, $eyeHeight - 8).'" fill="'.$ink.'" stroke="none"/>';
            $eyes .= '<circle cx="'.($x - 5).'" cy="'.(216 - $eyeHeight / 4).'" r="4" fill="#fff" stroke="none"/>';
            $eyes .= '<path d="M'.($x - 23).' 181Q'.$x.' 170 '.($x + 23).' 181" fill="none" stroke-width="5"/>';
        }
        $eyes .= $furry
            ? '<path d="M239 267Q256 259 273 267L256 282Z" fill="'.$ink.'"/><path d="M256 282V291M237 291Q247 304 256 291Q265 304 275 291" fill="none"/>'
            : '<path d="M252 249L247 263L257 265M239 289Q257 302 274 288" fill="none" stroke-width="3"/>';

        $hair = [
            'M101 201Q91 134 119 101Q154 53 253 57Q355 54 393 112Q414 151 398 202L374 175L367 203Q331 178 317 129Q288 175 238 193L249 153Q205 188 166 192L179 164L139 202L131 179Z',
            'M112 198Q97 124 131 91Q175 48 253 56Q339 50 379 94Q413 139 405 202L408 301L362 286L366 170Q306 161 275 121Q239 164 152 183L146 288L102 309Z',
            'M107 182Q115 99 192 74Q196 21 248 21Q301 16 318 73Q407 93 402 188L372 165L350 180Q317 148 304 118Q271 156 211 174L226 141Q177 170 107 182Z',
        ][$variant % 3];
        if ($furry) {
            $hair = [
                'M152 158Q185 107 249 107Q319 106 356 160L306 149L285 180L273 152L225 183L233 150Z',
                'M137 157Q185 104 250 102Q314 101 375 155L368 196L341 167L317 181L310 146L257 175L227 157L184 183L171 153Z',
                'M152 161Q188 125 224 119Q213 73 244 50Q294 66 303 115Q335 132 360 161L311 153L284 181L272 155L229 172L234 150Z',
            ][$variant % 3];
        }
        $highlight = $furry ? 'M189 142Q233 114 270 128M296 133L323 145' : 'M151 133Q196 76 268 82M290 85Q343 92 366 132';

        $markup = match ($category) {
            'background' => '<path fill="'.$color.'" stroke="none" d="M0 0H512V512H0Z"/>'.($variant === 0 ? '<g fill="none" stroke="#fff" opacity=".65"><circle cx="256" cy="229" r="192"/><circle cx="256" cy="229" r="180"/><path d="M53 103h32m-16-16v32m355 230h30m-15-15v30M435 79h26m-13-13v26M51 331h28m-14-14v28"/></g>' : ''),
            'body' => $body,
            'clothes' => '<path d="'.$garment.'" fill="'.$color.'"/>'.$details,
            'face' => $eyes,
            'hair' => '<path d="'.$hair.'" fill="'.$color.'"/><path d="'.$highlight.'" fill="none" stroke="#fff" opacity=".26" stroke-width="8"/>',
            'accessory' => [
                '<path d="M131 207Q177 198 220 208L215 231Q208 249 177 249Q145 249 137 230ZM292 208Q335 198 381 207L375 230Q367 249 335 249Q304 249 297 231ZM220 212Q256 195 292 212" fill="none" stroke="'.$color.'" stroke-width="7"/>',
                '<path d="M351 132L358 145L374 147L363 158L366 173L351 165L337 173L340 158L328 147L344 145Z" fill="'.$color.'"/>',
                '<path d="M127 270L139 290L127 310L115 290ZM385 270L397 290L385 310L373 290Z" fill="'.$color.'"/>',
            ][$variant],
        };

        return '<svg xmlns="http://www.w3.org/2000/svg" width="512" height="512" viewBox="0 0 512 512"><g stroke="'.$ink.'" stroke-width="4" stroke-linejoin="round" stroke-linecap="round">'.$markup.'</g></svg>';
    }
}
