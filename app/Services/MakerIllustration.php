<?php

namespace App\Services;

/** Artwork coordinates are shared by the layers of each individual maker. */
final class MakerIllustration
{
    public function __construct(
        private string $style,
        private int $variant,
        private string $color,
    ) {}

    public function render(string $category, bool $back): string
    {
        $ink = match ($this->style) {
            'chibi' => '#393451',
            'anime' => '#493241',
            'furry' => '#3b292c',
            'pony' => '#473761',
        };
        $art = match (true) {
            $category === 'background' => $this->background(),
            $this->style === 'chibi' => $this->chibi($category, $back),
            $this->style === 'anime' => $this->anime($category, $back),
            $this->style === 'furry' => $this->furry($category),
            $this->style === 'pony' => $this->pony($category, $back),
        };
        $width = $this->style === 'anime' ? 2.2 : 3;

        return '<svg xmlns="http://www.w3.org/2000/svg" width="512" height="512" viewBox="0 0 512 512"><g stroke="'.$ink.'" stroke-width="'.$width.'" stroke-linecap="round" stroke-linejoin="round">'.$art.'</g></svg>';
    }

    private function path(string $d, string $fill, string $attributes = ''): string
    {
        return '<path d="'.$d.'" fill="'.$fill.'" '.$attributes.'/>';
    }

    private function ellipse(float $x, float $y, float $rx, float $ry, string $fill, string $attributes = ''): string
    {
        return '<ellipse cx="'.$x.'" cy="'.$y.'" rx="'.$rx.'" ry="'.$ry.'" fill="'.$fill.'" '.$attributes.'/>';
    }

    private function tint(string $color, float $amount): string
    {
        $hex = '#';
        foreach ([1, 3, 5] as $offset) {
            $channel = hexdec(substr($color, $offset, 2));
            $target = $amount < 0 ? 0 : 255;
            $hex .= sprintf('%02x', (int) round($channel + ($target - $channel) * abs($amount)));
        }
        return $hex;
    }

    private function star(int $x, int $y, float $size, string $color): string
    {
        return '<g transform="translate('.$x.' '.$y.') scale('.$size.')">'.$this->path('M0-12Q2-2 12 0Q2 2 0 12Q-2 2-12 0Q-2-2 0-12Z', $color, 'stroke="none"').'</g>';
    }

    private function background(): string
    {
        $c = $this->color;
        $pale = $this->tint($c, .67);
        $art = '<defs><linearGradient id="sky" x2=".7" y2="1"><stop stop-color="'.$pale.'"/><stop offset="1" stop-color="'.$c.'"/></linearGradient></defs>';
        $art .= $this->path('M0 0H512V512H0Z', $this->variant === 1 ? $c : 'url(#sky)', 'stroke="none"');
        if ($this->variant === 1) {
            return $art;
        }
        if ($this->variant === 2) {
            $art .= '<defs><pattern id="check" width="36" height="36" patternUnits="userSpaceOnUse"><path d="M0 0h18v18H0zm18 18h18v18H18z" fill="#fff" opacity=".24"/></pattern></defs><rect width="512" height="512" fill="url(#check)" stroke="none"/>';
        } elseif ($this->style === 'pony') {
            $art .= $this->path('M0 348Q80 294 158 359T318 341T512 332V512H0Z', $pale, 'stroke="none"');
            $art .= $this->path('M33 142Q32 129 51 127Q53 104 77 103Q96 103 106 123Q132 117 140 141ZM349 93Q354 76 372 76Q374 54 398 54Q422 55 426 77Q449 73 458 93Z', '#ffffff', 'stroke="none" opacity=".72"');
        } elseif ($this->style === 'anime') {
            $art .= $this->path('M18 430L407 40H499L103 455Z M173 512L512 175V202L200 512Z', '#fff9ff', 'stroke="none" opacity=".36"');
        } else {
            $art .= $this->ellipse(256, 247, 180, 199, $pale, 'stroke="#fff" opacity=".74"');
            $art .= $this->ellipse(256, 247, 168, 187, 'none', 'stroke="#fff" opacity=".66" stroke-width="1"');
        }
        foreach ([[79, 98, 1.3], [425, 112, 1.6], [397, 208, .7], [89, 301, .95], [441, 369, 1.1], [127, 393, .65]] as [$x, $y, $size]) {
            $art .= $this->star($x, $y, $size, '#fffafc');
        }
        if ($this->style !== 'anime') {
            $art .= $this->ellipse(257, 456, 113, 15, $this->tint($c, -.32), 'stroke="none" opacity=".21"');
        }
        return $art;
    }

    private function anime(string $category, bool $back): string
    {
        $c = $this->color;
        $shade = $this->tint($c, -.27);
        $light = $this->tint($c, .55);
        $v = $this->variant;
        if ($category === 'body') {
            return $this->path('M213 259L208 314Q185 325 166 343L173 512H369L391 419L430 512H468L433 411Q422 366 399 345L321 317L309 257Z', $c)
                .$this->path('M208 289Q253 313 313 277L321 315Q296 350 228 337L210 317Z', $shade, 'stroke="none" opacity=".55"')
                .$this->path('M144 137Q143 66 222 43Q300 19 352 65Q399 103 367 195L350 228Q332 265 287 295Q264 312 248 305Q215 292 192 262L164 230Q155 217 154 193Z', $c)
                .$this->path('M347 166Q378 151 376 179Q374 202 349 215L341 208Z', $c)
                .$this->path('M348 199Q365 192 366 176Q357 172 349 184', 'none', 'stroke="'.$shade.'" stroke-width="1.7"')
                .$this->path('M363 177Q351 239 313 271Q282 301 252 306Q270 316 311 286Q353 253 370 200Z', $shade, 'stroke="none" opacity=".43"')
                .$this->ellipse(194, 219, 18, 7, '#e299a6', 'stroke="none" opacity=".45"')
                .$this->ellipse(304, 218, 23, 8, '#e299a6', 'stroke="none" opacity=".49"')
                .$this->path('M234 205L222 223L234 226', 'none', 'stroke="'.$shade.'" stroke-width="1.8"')
                .$this->path('M93 425Q102 390 127 350L155 305L176 268L187 237Q191 231 196 237L203 247L214 251Q221 255 216 261L207 262L213 270Q216 275 209 278L201 275L193 292L186 304L171 347L152 405Q142 443 124 455Q106 466 96 451Q90 442 93 425Z', $c)
                .$this->path('M102 430Q112 430 129 400L171 312L186 289L198 257L208 255L202 270L209 274L202 279L191 300L167 361L145 433Q126 467 103 455Z', $shade, 'stroke="none" opacity=".48"')
                .$this->path('M186 266L194 251L205 253M188 279L199 263L207 264M184 292L192 284', 'none', 'stroke="'.$shade.'" stroke-width="1.7"');
        }
        if ($category === 'face') {
            return $this->animeFace();
        }
        if ($category === 'hair') {
            return $this->animeHair($back);
        }
        if ($category === 'clothes') {
            $base = 'M207 314Q228 345 267 346Q295 342 322 314L372 333Q405 341 418 366L438 412Q420 438 391 437L370 512H164L166 421L149 405L166 352Z';
            $neckline = 'M209 320Q230 354 266 354Q303 350 326 320';
            $cloth = $this->path($base, $c)
                .$this->path('M389 366Q395 404 390 435L370 512H305L323 481Q354 450 350 419Q370 421 389 366Z M170 394Q163 431 167 477L185 461L184 432Z', $shade, 'stroke="none" opacity=".49"')
                .$this->path('M380 389Q383 432 371 452M394 428Q415 428 430 410M177 445Q193 453 206 448M271 482Q304 476 329 455', 'none', 'stroke="'.$shade.'" stroke-width="1.8"');
            return $cloth.match ($v) {
                0 => $this->path($neckline, 'none', 'stroke="'.$light.'" stroke-width="7"')
                    .$this->path('M183 382Q201 373 218 381L213 411Q196 416 183 407Z', $light, 'stroke-width="1.2"'),
                1 => $this->path('M190 323Q190 298 220 306Q253 333 290 325Q308 320 319 306Q353 304 352 333Q326 371 265 374Q217 366 190 323Z', $shade)
                    .$this->path('M231 351L220 406M295 351L306 406M230 471H306L320 505H216Z', 'none', 'stroke="'.$light.'" stroke-width="3.5"'),
                2 => $this->path('M208 314L189 331L221 389L265 354L306 388L344 332L322 314Q299 346 265 354Q232 349 208 314Z', $light)
                    .$this->path('M264 354V512', 'none', 'stroke="'.$shade.'" stroke-width="3"').$this->ellipse(275, 394, 3, 3, '#fff').$this->ellipse(275, 432, 3, 3, '#fff'),
                3 => $this->path('M212 314L193 332L222 402L241 383L234 451L268 512H284L319 401L341 331L321 315Q294 351 268 363Z', $shade)
                    .$this->path('M199 338L227 392L241 381M333 338L307 388L297 377M269 366L275 512', 'none', 'stroke="'.$light.'" stroke-width="3"'),
                4 => $this->path('M200 329Q222 360 267 365Q313 357 336 330L361 339Q323 392 268 401Q215 391 184 350Z', '#fff5eb')
                    .$this->path('M267 391Q241 370 232 388Q228 413 261 406L251 437L270 425L288 440L277 405Q311 411 309 388Q302 372 273 392Z', $shade),
                5 => $this->path('M208 315Q235 342 267 344Q299 340 324 315L337 330L324 358L215 363L198 333Z', $light)
                    .$this->path('M217 365L202 511M317 363L336 511M195 385L185 480M342 384L355 470', 'none', 'stroke="'.$shade.'" stroke-width="2"'),
                6 => $this->path('M210 315L191 328Q221 399 269 396Q309 388 344 329L321 314Q302 353 270 363Q235 352 210 315Z', $light)
                    .$this->path('M210 395Q258 419 315 395M207 405Q258 431 318 405M211 453Q260 475 309 452', 'none', 'stroke="'.$shade.'" stroke-width="3"'),
            };
        }
        return match ($v) {
            0 => $this->path('M158 189Q179 177 214 186L211 203Q194 220 170 205ZM248 188Q279 177 323 181L319 203Q292 222 261 207ZM214 191Q233 182 248 192M323 186L355 174', 'none', 'stroke="'.$c.'" stroke-width="3"'),
            1 => $this->path('M330 107L352 113M328 115L349 122', 'none', 'stroke="'.$c.'" stroke-width="6"').$this->star(338, 105, .58, '#fff2d5'),
            2 => $this->path('M356 201V217L346 235L356 246L366 235L356 217', $c).$this->star(356, 234, .42, $light),
            3 => $this->path('M240 316Q273 329 314 302L319 314Q279 344 235 328Z', $c).$this->ellipse(281, 329, 5, 6, '#f5dfbd'),
            4 => $this->path('M331 92Q343 76 365 81L357 98Q379 98 377 114Q362 126 345 106L329 123L326 104Z', $c).$this->ellipse(344, 101, 8, 8, $light),
            5 => $this->path('M220 339Q257 384 306 341', 'none', 'stroke="'.$c.'" stroke-width="2"').$this->star(266, 372, .86, $c),
        };
    }

    private function animeFace(): string
    {
        $v = $this->variant;
        $c = $this->color;
        $art = '';
        foreach ([[189, 184, .8, -10], [283, 183, 1, -12]] as [$x, $y, $scale, $tilt]) {
            $art .= '<g transform="translate('.$x.' '.$y.') rotate('.$tilt.') scale('.$scale.')">';
            $art .= $this->path('M-25-24Q-10-33 15-29', 'none', 'stroke="#76535a" stroke-width="1.7"');
            if ($v === 3) {
                $art .= $this->path('M-29 3Q-12-13 3-12Q18-12 28-1M-27 2l-7-5m11 3l-8-8', 'none', 'stroke-width="3.5"').'</g>';
                continue;
            }
            $top = $v === 1 ? -10 : ($v === 2 ? -21 : -16);
            $eye = 'M-29-3Q-13 '.($top - 10).' 8 '.$top.'Q23 '.($top + 2).' 30-1Q21 19 0 20Q-20 21-29-3Z';
            $art .= $this->path($eye, '#fff9fc')
                .'<defs><clipPath id="eye'.$x.'"><path d="'.$eye.'"/></clipPath></defs><g clip-path="url(#eye'.$x.')">'
                .$this->ellipse(2, 0, 16, 21, $c, 'stroke-width="1.6"')
                .$this->ellipse(3, -6, 11, 14, $this->tint($c, -.63), 'stroke="none"')
                .$this->path('M-10 7Q3 19 16 5Q15 19 4 21Q-7 20-10 7Z', $this->tint($c, .53), 'stroke="none"')
                .$this->ellipse(-5, -9, 6, 5, '#fff', 'stroke="none"')
                .$this->ellipse(11, 4, 3, 4, '#fff', 'stroke="none"').'</g>'
                .$this->path('M-35-9Q-28-7-24-11Q-11 '.($top - 8).' 8 '.$top.'Q25 '.($top + 1).' 30-1M-31-15L-23-11M22-10L29-14', 'none', 'stroke-width="3.9"')
                .$this->path('M-18 15Q-10 20-3 19M17 14L21 11', 'none', 'stroke-width="1.1"').'</g>';
        }
        return $art.match ($v) {
            0, 2 => $this->path('M237 251Q252 252 269 242Q267 265 254 268Q243 268 237 251Z', '#b96277', 'stroke-width="1.4"')
                .$this->path('M242 253Q254 252 265 247L262 253Q251 259 243 257Z', '#fff1ef', 'stroke="none"')
                .$this->path('M245 262Q253 254 264 259Q258 267 252 266Z', '#e8909d', 'stroke="none"'),
            1 => $this->path('M243 252Q252 256 263 248', 'none', 'stroke-width="1.7"'),
            3 => $this->path('M240 249Q254 250 268 244Q266 264 252 263Q243 261 240 249Z', '#c17085'),
            4 => $this->path('M242 255Q254 244 262 251', 'none', 'stroke-width="1.7"'),
        };
    }

    private function animeHair(bool $back): string
    {
        $v = $this->variant;
        $c = $this->color;
        $dark = $this->tint($c, -.28);
        $shine = $this->tint($c, .62);
        if ($back) {
            $silhouette = [
                'M142 142Q143 60 228 34Q319 3 369 68Q398 110 378 191Q367 236 393 290Q420 337 459 381Q497 431 449 466Q429 481 408 477Q428 444 397 414Q345 372 323 314Q270 339 205 304Q166 338 153 387Q123 459 70 470Q81 424 94 390Q107 330 133 267Q150 221 142 142Z',
                'M140 152Q129 84 191 50Q263 13 326 37Q396 65 392 135L386 221Q405 257 400 302Q375 323 348 295Q335 327 302 305L216 321Q163 315 135 290Q113 269 129 229Z',
                'M141 152Q131 72 217 38Q274 16 334 45Q349 8 387 22Q418 35 413 76Q460 67 478 114Q499 179 455 248Q427 288 453 329Q406 312 408 266Q401 212 371 189L306 283L200 280Q137 241 141 152Z',
                'M142 147Q140 73 208 39Q273 8 336 41Q405 69 385 190Q369 239 398 272Q428 308 397 351Q378 379 399 404Q354 412 343 382Q322 387 303 360L192 324Q172 382 123 397Q86 405 81 379Q125 343 117 310Q108 278 133 231Z',
                'M144 153Q125 102 168 61Q206 25 252 34Q303 14 348 49Q388 75 393 139Q397 199 366 233L385 251L346 260L349 280L319 270L303 290L199 272L171 280L172 253L141 264L149 229L125 232Z',
                'M147 164Q115 111 145 79Q164 59 186 65Q212 28 261 34Q300 27 321 52Q352 34 379 53Q402 68 401 99Q416 138 387 171L383 213L405 266L376 263L364 300L336 286L318 309L191 289L162 306L161 272L133 269L147 224Z',
                'M144 151Q134 75 209 43Q273 13 344 50Q410 84 381 174L365 218Q373 248 397 274L419 286L432 327L422 359L440 393L428 428L440 464L418 482L397 470L401 444L385 418L392 388L373 360L379 328L353 301L332 279L210 300Q160 284 140 240Z',
            ][$v];
            return $this->path($silhouette, $dark)
                .$this->path('M163 126Q169 74 224 53M332 66Q379 94 367 157Q348 222 356 266', 'none', 'stroke="'.$shine.'" stroke-width="4.3" opacity=".55"');
        }
        $fringe = [
            'M145 159Q135 100 167 70Q209 27 257 32Q321 23 361 64Q397 97 376 164L353 183Q353 162 347 144Q338 155 325 158L326 120Q320 145 303 157L295 120Q284 150 262 154L247 122Q235 155 213 159L203 132Q196 155 177 166L175 148L160 177Z',
            'M145 159Q133 102 167 66Q207 25 272 33Q333 30 369 78Q393 109 374 165L351 188L349 155Q289 137 267 86Q248 139 205 158L179 166L168 194Z',
            'M145 160Q134 107 168 71Q203 30 260 31Q315 27 353 67Q388 96 374 159L348 179L337 152Q308 143 280 89Q274 138 231 152L209 149L203 128Q187 156 171 166L160 188Z',
            'M146 161Q135 106 166 70Q207 27 260 34Q319 20 365 71Q393 105 375 163L354 180L345 146Q310 151 286 120L271 145L251 136L230 149L208 142L187 163L173 151L159 181Z',
            'M145 162Q132 112 169 68Q212 24 269 32Q331 31 365 71Q389 102 371 164L346 176L340 149L318 147L296 110L289 145L254 152L243 106Q220 151 190 154L172 175L158 179Z',
            'M146 162Q134 109 167 71Q204 33 264 34Q328 26 364 72Q392 111 372 163L345 171L337 146L324 160Q302 151 285 124L266 148L248 139L231 153L205 143L190 162L172 154L160 182Z',
            'M146 162Q132 108 168 69Q208 28 261 33Q317 26 360 70Q388 98 372 159L349 176L340 153Q295 141 280 91Q250 140 206 153L180 159L164 178Z',
        ][$v];
        $art = $this->path($fringe, $c)
            .$this->path('M157 109Q182 61 236 52Q223 59 212 75Q179 83 163 122ZM259 52Q323 46 355 97Q335 83 322 84Q295 59 259 60Z', $shine, 'stroke="none" opacity=".78"')
            .$this->path('M160 132Q170 100 197 82M181 145Q190 111 209 98M219 139Q232 106 251 85M307 80Q326 102 332 124M337 80Q359 102 363 134', 'none', 'stroke="'.$dark.'" stroke-width="1.15" opacity=".7"');
        if (in_array($v, [0, 3, 6], true)) {
            $art .= $this->path('M346 147Q357 164 350 198Q335 246 336 292Q334 338 352 364Q322 353 317 321Q308 285 320 249Q335 201 332 177Z', $c)
                .$this->path('M343 174Q341 210 328 247Q318 288 324 316', 'none', 'stroke="'.$shine.'" stroke-width="3" opacity=".72"');
        }
        return $art;
    }

    private function chibi(string $category, bool $back): string
    {
        $c = $this->color;
        $shade = $this->tint($c, -.25);
        $light = $this->tint($c, .56);
        $v = $this->variant;

        if ($category === 'body') {
            return $this->path('M229 220L227 250L204 267L196 295L218 335L217 377L224 432Q211 441 212 452Q221 461 239 454L247 387L259 366L272 387L279 452Q295 462 308 450Q308 440 296 432L301 375L297 333L317 289L307 269L281 249L278 219Z', $c)
                .$this->path('M222 265Q200 260 190 283L177 322L156 346Q147 356 155 361Q162 365 172 356L198 335L217 303M300 267Q319 260 326 286L338 323L359 341Q367 348 360 355Q355 360 345 353L319 335L302 305', $c)
                .$this->path('M230 233Q254 253 280 233L281 248Q256 265 228 248Z', $shade, 'stroke="none" opacity=".55"')
                .$this->path('M147 155Q138 100 167 70Q198 35 254 34Q323 33 353 88Q376 127 364 168L357 202Q344 229 306 244Q277 256 256 254Q224 253 195 239Q161 224 152 195Z', $c)
                .$this->path('M153 190Q173 228 215 239Q247 251 270 249Q326 242 353 202Q341 229 306 244Q251 269 195 239Q161 224 153 190Z', $shade, 'stroke="none" opacity=".34"')
                .$this->ellipse(184, 204, 16, 7, '#e697a6', 'stroke="none" opacity=".50"')
                .$this->ellipse(327, 204, 16, 7, '#e697a6', 'stroke="none" opacity=".50"')
                .$this->path('M177 200l-2 6m9-7l-2 7m8-6l-2 6m132-6l2 6m4-7l2 7m4-6l2 6', 'none', 'stroke="#a75477" stroke-width="1.1" opacity=".48"');
        }
        if ($category === 'face') {
            return $this->chibiFace();
        }
        if ($category === 'hair') {
            return $this->chibiHair($back);
        }
        if ($category === 'clothes') {
            $top = 'M226 253Q254 270 282 253L310 268L325 286L308 305L296 297L293 331H220L216 298L202 308L187 291L205 269Z';
            $short = 'M220 326H294L305 384L273 390L257 365L240 390L210 384Z';
            $skirt = 'M220 323H294Q307 344 319 379Q309 394 292 389Q278 404 256 393Q234 405 220 390Q204 394 194 380Q204 346 220 323Z';
            $shoes = $this->path('M224 417L243 418L240 442Q247 458 231 460H214Q205 453 214 444L222 438ZM277 418L297 418L300 440Q312 448 308 456Q298 461 280 458Q273 452 279 440Z', $light)
                .$this->path('M216 447l23-1m42 0l23 3', 'none', 'stroke="'.$shade.'" stroke-width="2"');
            $art = match ($v) {
                0 => $this->path($top, $c).$this->path($skirt, $light)
                    .$this->path('M225 255Q232 248 239 250L257 262L275 250Q286 248 288 256Q274 278 257 269Q239 277 225 255Z M187 291L202 308L218 297L209 275Z M302 276L325 286L309 305L296 296Z', '#fff7f8')
                    .$this->path('M235 333Q227 361 223 387M250 337L246 391M272 337L279 390M289 334L300 385', 'none', 'stroke="'.$shade.'" stroke-width="2" opacity=".42"'),
                1 => $this->path($short, $shade).$this->path('M221 251Q255 269 291 249L318 271L332 303L316 319L301 307L301 355Q257 370 210 354L212 306L195 321L179 308L192 272Z', $c)
                    .$this->path('M224 255Q254 291 289 254M238 269L231 311M272 267L280 307M234 323H278L289 346H221Z', 'none', 'stroke="'.$shade.'"'),
                2 => $this->path($top, '#fff5f4').$this->path($short, $c)
                    .$this->path('M216 263L229 259L233 314H280L285 258L299 263L289 329H224Z', $c)
                    .$this->path('M238 326H274L271 344Q253 353 240 343Z', $light),
                3 => $this->path($skirt, $shade).$this->path($top, '#fff9f2')
                    .$this->path('M221 261L256 283L291 261L284 295L256 307L228 295Z', $c)
                    .$this->path('M251 294L233 318L254 313L266 321L265 295Z', $light)
                    .$this->path('M204 372Q256 387 309 372', 'none', 'stroke="'.$light.'" stroke-width="4"'),
                4 => $this->path('M227 253Q253 271 281 253L315 268L330 302L311 313L299 305L308 365Q260 387 203 365L213 306L197 315L182 302L200 270Z', $c)
                    .$this->path('M218 266L257 293L295 266L279 306L257 298L234 308Z M219 347H237V358H219Z M278 347H296V358H278Z', $light)
                    .$this->path('M257 297V373', 'none', 'stroke="'.$shade.'"'),
                5 => $this->path($top, $c).$this->path('M220 319H293L302 342L325 379L301 386L288 402L257 393L226 402L213 386L188 379L212 341Z', $light)
                    .$this->path('M225 264L244 275L256 269L268 275L285 264L280 303L257 321L232 303Z', $shade)
                    .$this->star(257, 287, .75, '#fff0b7'),
                6 => $this->path($short, $shade).$this->path($top, $c)
                    .$this->path('M193 288L207 297M213 278L222 298M318 281L306 294M222 317H292', 'none', 'stroke="'.$light.'" stroke-width="5"')
                    .$this->path('M231 262Q255 285 282 263', 'none', 'stroke="'.$light.'" stroke-width="7"'),
            };
            if ($v === 0) {
                foreach ([281, 293, 305] as $y) {
                    $art .= $this->ellipse(245, $y, 3, 3, '#fff', 'stroke="none"').$this->ellipse(268, $y, 3, 3, '#fff', 'stroke="none"');
                }
            }
            return $art.$shoes;
        }
        return match ($v) {
            0 => $this->path('M162 170Q188 153 217 165L226 171L221 190Q212 204 190 201Q171 199 167 184ZM286 171Q313 156 344 165L339 188Q332 203 311 201Q291 197 289 185ZM226 175Q255 162 287 175', 'none', 'stroke="'.$c.'" stroke-width="4"'),
            1 => $this->star(326, 119, 1.55, $c).$this->star(347, 108, .8, $light),
            2 => $this->ellipse(155, 211, 5, 7, $c).$this->ellipse(356, 211, 5, 7, $c),
            3 => $this->path('M186 79Q160 55 151 75Q140 102 174 103L190 93L210 105Q232 103 234 78Q229 60 198 76Z', $c).$this->ellipse(191, 87, 11, 11, $light),
            4 => $this->path('M162 114Q174 59 254 58Q333 58 350 114', 'none', 'stroke="'.$c.'" stroke-width="12"').$this->ellipse(161, 130, 14, 22, $c).$this->ellipse(352, 130, 14, 22, $c),
            5 => $this->path('M284 269Q253 309 207 351', 'none', 'stroke="'.$shade.'" stroke-width="5"')
                .$this->path('M187 351Q205 341 224 352L227 387Q208 397 188 391Z', $c).$this->star(206, 372, .85, $light),
        };
    }

    private function chibiFace(): string
    {
        $v = $this->variant;
        $c = $this->color;
        $art = '';
        foreach ([189, 322] as $x) {
            $art .= $this->path('M'.($x - 20).' 147Q'.$x.' 133 '.($x + 19).' 145', 'none', 'stroke-width="2.5"');
            if ($v === 3) {
                $art .= $this->path('M'.($x - 25).' 178Q'.$x.' 147 '.($x + 24).' 177', 'none', 'stroke-width="5"');
                continue;
            }
            $eye = match ($v) {
                1 => 'M-26 0Q-8-18 25-12Q27 12 11 21Q-13 27-26 0Z',
                2 => 'M-24 0Q-22-28 0-29Q24-27 25-3Q25 22 2 25Q-23 23-24 0Z',
                4 => 'M-26-6Q-13-25 4-23Q24-19 25 4Q15 26-4 23Q-21 21-26-6Z',
                default => 'M-26-3Q-13-24 6-21Q24-16 25 4Q15 25-5 22Q-23 18-26-3Z',
            };
            $art .= '<g transform="translate('.$x.' 177)">'.$this->path($eye, '#fffaff')
                .'<defs><clipPath id="chibi'.$x.'"><path d="'.$eye.'"/></clipPath></defs><g clip-path="url(#chibi'.$x.')">'
                .$this->ellipse(1, 1, 15, 19, $c, 'stroke-width="1.4"')
                .$this->ellipse(1, -3, 9, 12, $this->tint($c, -.67), 'stroke="none"')
                .$this->ellipse(4, 11, 9, 4, $this->tint($c, .54), 'stroke="none"')
                .$this->ellipse(-5, -10, 6, 5, '#fff', 'stroke="none"')
                .$this->ellipse(10, 4, 3, 3, '#fff', 'stroke="none"').'</g>'
                .$this->path('M-30-9Q-22-6-19-12M-25-4Q-11-24 6-21Q20-18 25-8', 'none', 'stroke-width="4"').'</g>';
        }
        return $art.match ($v) {
            0 => $this->ellipse(255, 221, 7, 10, '#db8490', 'stroke-width="1.4"'),
            1 => $this->path('M246 219Q255 227 265 219', 'none', 'stroke-width="2"'),
            2, 3 => $this->path('M241 216Q256 221 270 215Q265 235 254 234Q245 232 241 216Z', '#b66a80', 'stroke-width="1.5"'),
            4 => $this->path('M248 221Q255 215 264 221', 'none', 'stroke-width="2"'),
        };
    }

    private function chibiHair(bool $back): string
    {
        $v = $this->variant;
        $c = $this->color;
        $dark = $this->tint($c, -.25);
        if ($back) {
            $shape = [
                'M154 104Q170 45 255 41Q336 42 357 109Q374 177 368 254Q364 323 383 397Q360 390 352 425Q321 408 313 378Q295 343 290 291Q271 269 256 260Q237 271 222 296Q219 348 197 385Q187 411 156 430Q154 404 132 414Q147 358 144 305Q133 196 154 104Z',
                'M151 113Q186 50 254 52Q342 47 365 122Q382 187 353 269L328 287L317 271Q257 297 194 267L169 286Q131 253 139 185Z',
                'M219 86Q209 27 256 18Q302 20 311 64Q377 45 408 97Q446 157 406 249Q393 204 357 192Q321 174 310 121Z',
                'M171 105Q144 61 113 85Q72 105 90 180Q101 212 82 266Q121 263 139 228Q164 251 158 289Q189 272 192 216ZM334 99Q374 64 405 95Q439 126 420 185Q405 226 429 264Q391 264 371 228Q345 255 351 287Q324 267 321 217Z',
                'M161 106Q193 45 258 42Q348 42 369 117Q385 189 355 264Q388 287 371 327Q400 366 362 401Q349 437 298 419Q273 450 228 427Q188 444 160 412Q120 414 121 373Q96 350 131 321Q113 282 149 250Z',
                'M157 119Q195 52 254 52Q325 47 361 110Q393 181 353 247Q324 273 290 247L222 250Q184 280 151 246Q126 203 157 119Z',
                'M154 110Q175 42 256 44Q344 49 363 115L379 270Q392 323 370 376L352 389Q351 324 324 301L313 273L196 272L180 315Q149 351 153 390L134 377Q114 335 132 272Z',
            ][$v];
            $art = $this->path($shape, $dark);
            if ($v === 0) {
                $art .= $this->path('M157 186Q149 267 161 328Q166 364 158 399M179 219Q181 258 174 298Q170 346 180 377M351 193Q357 255 347 313Q343 360 357 389M330 229Q335 277 326 314Q320 351 337 383', 'none',
                    'stroke="'.$this->tint($c, .37).'" stroke-width="3" opacity=".53"');
            }
            return $art;
        }
        $fringe = [
            'M148 160Q139 102 167 70Q198 31 253 33Q314 31 346 74Q374 107 364 173L346 162L338 130L335 155Q312 145 305 124L308 159L272 165L251 119L252 164L217 165L207 126L199 154L170 169L170 138L158 177Z',
            'M148 173Q136 112 166 75Q211 23 281 38Q343 50 362 96Q375 128 358 184L346 208L336 160Q301 158 271 103Q240 148 180 162L176 213Q151 201 148 173Z',
            'M147 170Q135 111 169 73Q211 26 276 38Q343 48 363 101Q375 131 357 168L334 153Q296 145 276 101Q264 145 226 154L230 126Q207 156 171 158L159 179Z',
            'M149 166Q140 92 181 61Q223 25 282 38Q352 54 363 122L361 171L341 155L332 120L316 153L290 145L272 120L252 156L224 152L215 125Q196 154 171 155L161 178Z',
            'M149 175Q137 141 148 105Q147 75 183 64Q200 35 239 42Q268 21 302 46Q339 48 350 80Q380 102 366 147Q370 165 351 178L339 149Q305 160 284 125Q273 148 248 149L236 131Q210 163 178 148L163 176Z',
            'M149 166Q139 127 155 93Q176 50 226 44Q244 20 268 39Q321 34 349 79Q377 110 360 167L333 159L329 142L307 149L286 106L275 145L251 150L245 115Q218 149 178 154L160 174Z',
            'M148 162Q140 97 171 69Q210 28 275 37Q341 46 362 110L365 167L347 184L337 145Q303 137 278 86Q249 142 173 154L159 173Z',
        ][$v];
        // Keep the front hair joined across the crown even when the back shape is wider.
        $cap = 'M148 141Q135 91 170 65Q203 29 254 30Q326 28 352 77Q374 103 365 144L347 126Q314 88 257 86Q193 88 153 143Z';
        $art = $this->path($cap, $c, 'stroke="none"').$this->path($fringe, $c).$this->path('M165 101Q191 57 237 55M255 54Q302 52 335 92', 'none', 'stroke="'.$this->tint($c, .58).'" stroke-width="6" opacity=".72"')
            .$this->path('M174 119Q194 94 214 88M314 89Q337 111 343 132', 'none', 'stroke="'.$dark.'" stroke-width="1.6" opacity=".55"');
        if ($v === 6) {
            foreach ([176, 335] as $x) {
                $art .= '<g transform="translate('.$x.' 147)">'.$this->path('M-10 0Q-20 33-8 55Q-18 69-8 83Q-18 98-7 111Q-13 124 0 137Q13 124 7 111Q18 98 8 83Q18 69 8 55Q20 33 10 0Z', $c).$this->path('M-8 55l16 8m-16 20l16 8m-15 20l14 6', 'none', 'stroke="'.$dark.'" stroke-width="2"').'</g>';
            }
        }
        return $art;
    }

    private function furry(string $category): string
    {
        $c = $this->color;
        $dark = $this->tint($c, -.30);
        $light = $this->tint($c, .60);
        $v = $this->variant;
        if ($category === 'body') {
            $species = $v % 6;
            $coat = intdiv($v, 6);
            $ears = [
                'M137 159Q70 119 75 45Q151 63 199 126ZM318 119Q371 54 440 49Q451 119 397 162Z',
                'M147 151Q105 96 105 50Q154 51 194 120ZM317 122Q355 64 406 54Q411 101 380 156Z',
                'M139 165Q71 145 82 76Q116 47 163 103L194 125ZM321 128Q356 65 402 74Q449 97 406 162Z',
                'M145 165Q100 113 115 50Q122 8 150 27Q185 53 190 130ZM319 130Q329 48 366 25Q395 17 402 47Q412 103 378 161Z',
                'M132 171Q66 167 63 104Q60 59 105 59Q154 60 180 120ZM331 124Q361 60 413 62Q455 63 452 107Q450 154 402 174Z',
                'M146 161Q90 147 94 87Q100 49 146 70L198 120ZM315 124Q344 64 391 69Q432 77 425 121Q422 146 396 166Z',
            ][$species];
            $inner = [
                'M94 68Q140 85 169 122L137 141Q105 116 94 68ZM350 122Q383 90 423 70Q416 121 389 143Z',
                'M119 67Q147 73 170 117L147 128Q122 106 119 67ZM342 121Q368 83 391 73Q389 112 374 133Z',
                'M98 92Q113 75 143 109L152 126L121 146Q95 124 98 92ZM359 122Q390 84 408 97Q428 112 401 146Z',
                'M133 44Q153 45 165 87L174 124L152 136Q122 87 133 44ZM340 123Q345 78 371 49Q386 43 389 60Q394 96 375 131Z',
                'M81 105Q79 76 107 78Q137 82 153 111L118 141Q88 143 81 105ZM360 112Q376 81 410 82Q434 83 434 106Q430 138 408 145Z',
                'M109 93Q116 73 140 91L171 119L141 140Q112 133 109 93ZM342 121Q367 83 393 87Q420 93 401 133L383 145Z',
            ][$species];
            $head = [
                'M121 159Q165 106 251 108Q337 101 393 154L411 225L434 252L402 251L414 273L380 268Q326 305 280 330Q255 345 233 332Q174 313 119 268L91 274L104 251L78 253L111 219Z',
                'M121 161Q170 102 252 110Q343 108 392 157L404 227L414 251L393 246Q378 292 313 319Q256 346 202 319Q137 291 120 250L96 253L105 226Z',
                'M117 162Q155 106 247 105Q346 100 398 161L416 214L438 245L405 241L423 276L392 267L389 288L360 285Q299 333 253 340Q190 329 145 283L114 290L117 265L87 273L106 243L78 248L101 211Z',
                'M117 173Q143 110 252 108Q359 109 396 174L405 228Q413 275 356 304Q300 338 252 340Q199 337 147 304Q95 276 105 229Z',
                'M111 171Q150 109 251 110Q355 107 401 171Q433 219 405 276Q368 332 254 343Q145 335 109 278Q85 223 111 171Z',
                'M117 161Q174 106 252 108Q332 100 391 156L414 214L433 242L405 249L410 269L381 268Q335 316 252 337Q168 322 120 272L94 270L104 247L80 245L100 216Z',
            ][$species];
            $muzzle = $species === 2 ? 'M167 256Q212 231 251 259Q292 232 340 257Q323 312 253 330Q190 317 167 256Z'
                : 'M111 244Q170 224 251 265Q334 224 399 246Q376 304 252 331Q145 308 111 244Z';
            $art = $this->path('M209 312L201 367L144 386Q80 420 66 512H446Q430 422 362 385L309 368L302 313Z', $dark)
                .$this->path($ears, $dark).$this->path($inner, '#e2a8aa', 'stroke-width="2"')
                .$this->path($head, $c).$this->path($muzzle, $light, 'stroke="none" opacity=".95"');
            $art .= match ($coat) {
                1 => $this->path('M215 111Q182 153 198 187L214 162L228 193L253 149L280 187L290 156L311 182Q319 145 295 109Z M113 229L151 239L118 251ZM392 225L365 238L398 252Z', $dark, 'stroke="none" opacity=".55"'),
                2 => $this->path('M121 165Q145 127 206 114Q204 144 177 166Q147 197 117 201ZM301 119Q360 124 390 171L402 200Q364 191 340 163Z', $dark, 'stroke="none" opacity=".62"')
                    .$this->ellipse(191, 138, 7, 5, $light, 'stroke="none"').$this->ellipse(321, 139, 8, 5, $light, 'stroke="none"'),
                default => '',
            };
            if ($species === 5) {
                $art .= $this->path('M121 181Q169 161 220 208L231 242Q186 253 135 231ZM382 178Q336 163 285 209L276 242Q331 255 386 232Z', $dark, 'stroke="none" opacity=".78"');
            }
            if ($species === 1) {
                $art .= $this->path('M171 260l-56-9m56 21l-54 5m214-17l56-9m-57 21l56 5', 'none', 'stroke-width="1.9"');
            }
            if ($species === 3) {
                $art .= $this->path('M246 285H262L261 298H246Z', '#fff7f2', 'stroke-width="1.2"');
            }
            return $art.$this->path($species === 3 ? 'M240 265Q254 257 266 265L254 274Z' : 'M236 262Q253 251 272 262Q267 274 254 279Q241 275 236 262Z', '#4a3035')
                .$this->path('M254 278V285M254 285Q243 296 229 283M254 285Q266 296 279 283', 'none', 'stroke-width="2"');
        }
        if ($category === 'face') {
            $art = '';
            foreach ([178, 328] as $x) {
                $ry = [18, 13, 24, 15, 21][$v];
                $art .= $this->ellipse($x, 216, 28, $ry, '#fff3de').$this->ellipse($x, 217, 13, $ry - 1, $c, 'stroke-width="1.6"')
                    .$this->ellipse($x, 216, $v === 1 ? 3 : 6, $ry - 6, '#37252d', 'stroke="none"')
                    .$this->ellipse($x - 5, 210, 4, 4, '#fff', 'stroke="none"')
                    .$this->path('M'.($x - 24).' 188Q'.$x.' '.($v === 3 ? 175 : 183).' '.($x + 23).' 187', 'none', 'stroke-width="3.1"');
            }
            return $art;
        }
        if ($category === 'hair') {
            $tuft = [
                'M163 158Q185 116 249 113Q309 105 347 150L317 144L293 172L283 145L252 174L256 147L220 174L228 146L193 163Z',
                'M158 160Q180 119 245 113Q304 99 350 147L333 172L314 153L302 166L287 132Q274 166 249 179L252 146L220 170L195 156L172 174Z',
                'M162 161Q191 125 228 119Q219 88 246 73Q289 86 301 119Q333 127 350 153L319 148L288 175L282 147L244 169L252 143L217 171Z',
                'M164 163Q162 140 182 130Q182 109 215 111Q231 91 251 110Q275 94 298 113Q320 108 336 128Q354 140 346 157Q330 175 309 160Q291 175 273 155Q249 174 231 155Q207 177 189 155Z',
                'M159 163Q189 124 240 111L228 92L258 105L274 89L278 111L313 101L306 122Q334 132 349 154L325 154L307 179L293 150L273 168L258 139L223 173L221 148L193 169Z',
                'M164 164Q181 128 226 119Q257 106 297 114Q336 126 350 153L327 158L314 173L288 148L280 171L251 147L229 169L213 152L184 167Z',
            ][$v];
            $crown = 'M118 185Q122 152 148 131Q180 104 225 102Q252 96 272 103Q330 100 366 131Q386 148 392 180Q378 168 369 152Q354 156 345 163Q329 145 313 141Q254 133 199 145Q180 148 164 165Q156 160 151 155Q136 160 118 185Z';
            return $this->path($crown, $c).$this->path($tuft, $c, 'stroke="none"')
                .$this->path('M154 143Q190 114 230 117M247 115Q290 109 331 128', 'none', 'stroke="'.$light.'" stroke-width="4" opacity=".68"');
        }
        if ($category === 'clothes') {
            $cape = 'M200 360Q254 386 311 360L374 381Q432 405 450 512H61Q80 410 135 383Z';
            $art = $this->path($cape, $c).$this->path('M115 424Q99 459 99 512M390 424Q410 466 411 512', 'none', 'stroke="'.$dark.'" stroke-width="2"');
            return $art.match ($v) {
                0 => $this->path('M197 364L181 392L223 426L252 399L283 426L324 389L310 362Q254 389 197 364Z', $light).$this->path('M252 399V512', 'none', 'stroke="'.$dark.'"'),
                1 => $this->path('M167 374Q254 432 338 373L327 409Q253 449 176 407Z', $dark).$this->path('M227 418H280L292 462H215Z', $light),
                2 => $this->path('M197 361Q250 405 315 361L344 388Q307 431 251 436Q196 432 166 391Z', $light).$this->star(252, 436, 1.4, '#f8d591'),
                3 => $this->path('M198 360L181 383L214 449L253 409L297 447L329 381L312 360Q248 390 198 360Z', $dark).$this->path('M254 412L264 512', 'none', 'stroke="'.$light.'" stroke-width="4"'),
                4 => $this->path('M151 391Q208 411 253 447Q303 406 362 389L349 418Q296 426 253 474Q206 430 164 422Z', $light).$this->ellipse(253, 470, 10, 11, '#f3d698'),
                5 => $this->path('M192 362Q250 395 314 362L336 379L319 410Q246 431 179 405L170 383Z', $light).$this->path('M233 410L273 411L292 504L273 495L259 508Z', $dark),
            };
        }
        return match ($v) {
            0 => $this->path('M141 206L212 205L208 231Q175 252 148 230ZM293 205L365 204L357 232Q329 249 298 231ZM212 212Q254 195 293 212', 'none', 'stroke="'.$c.'" stroke-width="5"'),
            1 => $this->star(319, 145, 1.1, $c).$this->star(340, 141, .66, $light),
            2 => $this->ellipse(123, 166, 6, 13, $c).$this->ellipse(391, 166, 6, 13, $c),
            3 => $this->path('M214 347Q253 368 298 346L301 358Q252 381 209 359Z', $c).$this->ellipse(253, 371, 9, 10, '#f6d998'),
            4 => $this->path('M134 258l31-4m-26 15l29-3m173-12l32 4m-34 8l29 3', 'none', 'stroke="'.$c.'" stroke-width="3.5"'),
            5 => $this->path('M247 378Q215 348 203 368Q200 393 244 390L225 414L248 403L259 419L264 389Q305 395 306 369Q293 351 265 375Z', $c).$this->ellipse(254, 383, 12, 10, $light),
        };
    }

    private function pony(string $category, bool $back): string
    {
        $c = $this->color;
        $dark = $this->tint($c, -.25);
        $light = $this->tint($c, .59);
        $v = $this->variant;
        if ($category === 'body') {
            return $this->path('M265 302Q292 278 341 287Q388 297 382 334L359 372L345 410L345 431Q331 442 309 437L300 431L309 404L310 368L249 368L233 409L226 435Q214 446 192 441L182 434L194 405L196 344Z', $dark)
                .$this->path('M204 246L220 278Q239 294 267 284Q312 265 353 287Q386 305 373 344Q367 362 354 374L369 432Q364 453 332 452L310 445L313 427L299 375Q268 388 233 369L213 419L202 446Q184 458 158 450L149 439L166 412L179 355Q149 331 160 286L154 266Z', $c)
                .$this->path('M168 398Q176 369 180 356Q196 363 210 360L192 413L185 445Q168 451 153 442ZM331 376Q357 366 372 339L353 375L367 436Q362 447 344 448Z', $dark, 'stroke="none" opacity=".47"')
                .$this->path('M113 177Q129 135 183 129L192 113Q189 85 217 69Q243 72 245 112L239 132Q271 150 265 197Q260 232 237 254Q209 278 168 273Q133 270 120 245Q98 249 84 233Q73 218 85 202L113 196Z', $c)
                .$this->path('M207 120Q200 93 217 83Q228 86 230 104L224 124Z', $dark, 'stroke-width="1.4"')
                .$this->path('M90 212Q108 208 127 213Q121 225 129 242Q110 248 91 235Q82 226 90 212Z', $light, 'stroke="none"')
                .$this->path('M92 232Q112 239 127 229M102 216l4 1M168 441Q185 445 200 440M319 441Q343 447 365 439', 'none', 'stroke="'.$dark.'" stroke-width="2"')
                .$this->ellipse(147, 234, 14, 6, '#e9a5ba', 'stroke="none" opacity=".60"');
        }
        if ($category === 'face') {
            if ($v === 3) {
                return $this->path('M142 199Q156 168 177 176Q190 178 198 194M145 194L136 188M151 185L143 178', 'none', 'stroke-width="5"')
                    .$this->path('M98 234Q112 241 129 230Q127 248 113 249Q104 249 98 234Z', '#b96d8b', 'stroke-width="1.4"');
            }
            $eye = [
                'M141 194Q141 165 164 158Q192 153 200 185Q205 215 171 223Q144 225 141 194Z',
                'M141 200Q143 178 164 174Q189 168 198 190Q193 219 165 222Q145 219 141 200Z',
                'M138 190Q139 155 169 151Q199 152 203 184Q206 220 173 225Q142 228 138 190Z',
                '',
                'M143 192Q144 161 171 162Q197 166 195 198Q192 223 169 224Q144 222 143 192Z',
            ][$v];
            $art = $this->path($eye, '#fffaff').'<defs><clipPath id="iris"><path d="'.$eye.'"/></clipPath></defs><g clip-path="url(#iris)">'
                .$this->ellipse(164, 191, 21, 30, $c).$this->ellipse(159, 186, 12, 18, '#33233e', 'stroke="none"')
                .$this->ellipse(170, 208, 11, 7, $light, 'stroke="none"').$this->ellipse(154, 176, 7, 7, '#fff', 'stroke="none"').$this->ellipse(172, 198, 4, 4, '#fff', 'stroke="none"').'</g>'
                .$this->path([
                    'M136 185Q143 161 165 157Q187 157 199 183',
                    'M137 190Q151 175 170 173Q187 174 198 189',
                    'M136 181Q144 155 169 151Q191 150 202 181',
                    '',
                    'M141 184Q149 161 171 162Q192 166 195 187',
                ][$v], 'none', 'stroke-width="4"')
                .$this->path('M174 147Q188 144 197 150', 'none', 'stroke-width="2.4"');
            return $v === 4 ? $art.$this->ellipse(113, 234, 6, 8, '#b96d8b', 'stroke-width="1.4"') : $art;
        }
        if ($category === 'hair') {
            $tail = [
                'M344 297Q364 251 405 260Q440 266 442 307Q449 348 478 345Q481 375 458 393Q426 410 399 385Q380 363 381 333Q375 310 344 315Z',
                'M341 303Q385 242 432 269Q453 286 450 320Q446 349 478 375Q458 397 433 384L425 403Q395 387 392 358Q395 323 341 313Z',
                'M344 296Q372 266 403 263Q444 257 455 286Q470 318 448 336Q473 349 465 373Q454 395 431 389Q409 399 397 380Q387 368 398 354Q373 339 380 316Z',
                'M342 301Q383 264 414 280Q436 291 431 318L444 332L440 352L455 371L445 392L419 398L415 377L400 357L407 338L391 323L395 308Q368 305 342 315Z',
                'M342 299Q376 259 414 270Q444 273 461 295Q446 290 443 309Q451 335 470 345Q452 360 432 349L429 369Q399 362 392 331Q379 309 342 313Z',
                'M342 301Q377 266 421 276Q455 284 452 313Q449 346 477 356Q472 393 440 399Q402 406 392 370Q385 349 405 338Q391 320 342 315Z',
            ][$v];
            if ($back) {
                return $this->path($tail, $dark)
                    .$this->path('M197 119Q239 92 274 132Q301 159 285 210Q267 248 292 286Q305 306 286 332Q264 353 233 333Q219 317 218 288L189 240Z', $dark)
                    .$this->path('M373 298Q413 276 426 308Q429 345 449 365M253 145Q272 172 258 217Q244 252 264 280', 'none', 'stroke="'.$light.'" stroke-width="5" opacity=".64"');
            }
            $mane = [
                'M113 176Q108 147 136 123Q151 102 186 107Q209 120 229 120Q244 130 241 155L229 168L212 158L204 175L186 153Q165 179 139 169L126 188Z',
                'M112 173Q109 147 136 122Q166 99 189 109Q219 119 239 131L245 156L227 188L211 166L213 149Q180 172 142 166L124 183Z',
                'M114 176Q102 159 118 140Q120 119 143 119Q152 99 175 107Q199 95 211 117Q238 113 243 140Q252 160 229 172Q218 180 205 166Q185 181 174 163Q154 179 139 169L125 185Z',
                'M113 174Q109 146 140 122Q165 101 190 108Q231 111 241 143L236 170L222 184L213 168L199 171L185 152Q160 173 138 168L123 184Z',
                'M113 173Q105 143 141 120L138 107L161 108L170 94L188 110L207 107L214 120Q241 124 243 148L228 169L213 154L207 177L186 148Q160 173 137 166L123 184Z',
                'M112 176Q107 144 137 121Q161 99 190 108Q230 114 241 142Q247 164 222 181L208 167L206 146Q174 172 143 168L126 187Z',
            ][$v];
            $sideLock = 'M231 126Q259 129 265 159Q274 187 258 215Q248 231 248 248Q229 237 231 213Q234 194 227 173L218 153Z';
            return $this->path($sideLock, $c).$this->path($mane, $c)
                .$this->path('M126 153Q148 118 180 118Q215 121 230 141M245 146Q262 164 253 196', 'none', 'stroke="'.$light.'" stroke-width="4.5" opacity=".78"');
        }
        if ($category === 'clothes') {
            return match ($v) {
                0 => $this->path('M170 268Q198 274 225 255L240 268L228 287Q199 303 163 288Z', $c).$this->path('M202 285L218 294L212 326L200 319L191 328L188 302Z', $dark),
                1 => $this->path('M219 282Q262 284 287 270L300 282L290 332L263 361L231 347L228 322L206 313Z', $c).$this->path('M221 306Q255 329 287 310M232 337L269 350', 'none', 'stroke="'.$light.'" stroke-width="5"'),
                2 => $this->path('M171 270Q192 277 225 256L237 270Q207 298 171 291Z', $c).$this->ellipse(198, 294, 9, 12, '#ffdea1'),
                3 => $this->path('M151 431L205 433L202 449Q180 459 151 451ZM312 433L366 431L369 446Q349 460 312 449Z', $c).$this->path('M162 441l32 2m128-1l36-2', 'none', 'stroke="'.$light.'" stroke-width="3"'),
                4 => $this->path('M302 308Q281 289 274 306Q269 326 298 325L286 350L305 341L321 353L316 325Q343 333 345 308Q339 288 313 308Z', $c).$this->ellipse(308, 316, 9, 10, $light),
                5 => $this->path('M221 289Q250 299 282 284Q302 283 312 299L299 334Q268 362 226 340L215 312Z', $c).$this->star(260, 319, 1.4, $light),
            };
        }
        $horn = $this->path('M141 144Q151 118 154 86Q169 103 171 139Q158 151 141 144Z', $c)
            .$this->path('M149 127L166 119M145 139L169 131M153 115L162 111', 'none', 'stroke="'.$dark.'" stroke-width="1.7"');
        $wing = $this->path('M262 305Q270 281 294 272Q320 262 340 234Q358 212 385 209Q390 222 366 244Q385 239 399 248Q397 264 368 274Q387 273 390 284Q375 300 352 301Q366 311 349 320Q313 339 281 337Q259 333 262 305Z', $c)
            .$this->path('M272 314Q293 314 306 303Q312 289 328 285M314 316Q345 304 351 290M327 298Q346 290 366 274', 'none', 'stroke="'.$dark.'" stroke-width="1.8"')
            .$this->path('M325 272Q354 246 369 235Q351 258 339 268Z', $light, 'stroke="none" opacity=".76"');
        return match ($v) {
            0 => $horn,
            1 => $wing,
            2 => $wing.$horn,
            3 => $this->path('M139 131Q160 111 187 121L186 141Q162 148 138 144Z', $c).$this->star(160, 124, .88, '#ffeac1'),
        };
    }
}
