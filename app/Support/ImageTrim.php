<?php

namespace App\Support;

use GdImage;

/**
 * Cuts the white margin a picture was scanned or cropped with (photos taken from the PDF catalogue
 * often keep a white strip on one side). Only near-white rows and columns at the edges go; the
 * picture itself is untouched, and a cut-out part on a white background is left as it is.
 * Works on JPEG, PNG and WebP with GD.
 */
final class ImageTrim
{
    /** Every channel above this is «white». */
    private const WHITE = 238;

    /** A row or column with at most this share of non-white pixels still counts as margin (dust, JPEG noise). */
    private const NOISE = 0.01;

    /** Margins thinner than this are left alone. */
    private const MIN_PX = 3;

    /** A stray strip is thin: when more than this share of a side would go, it is a part on white, not a strip. */
    private const MAX_CUT = 0.15;

    /**
     * @return array{0: int, 1: int, 2: int, 3: int}|null crop box [x, y, width, height], or null when there is nothing to cut
     */
    public static function box(GdImage $image): ?array
    {
        $w = imagesx($image);
        $h = imagesy($image);

        $blankRow = fn (int $y): bool => self::blank($image, $w, fn (int $i) => [$i, $y]);
        $blankCol = fn (int $x): bool => self::blank($image, $h, fn (int $i) => [$x, $i]);

        $top = 0;
        while ($top < $h - 1 && $blankRow($top)) {
            $top++;
        }
        $bottom = $h - 1;
        while ($bottom > $top && $blankRow($bottom)) {
            $bottom--;
        }
        $left = 0;
        while ($left < $w - 1 && $blankCol($left)) {
            $left++;
        }
        $right = $w - 1;
        while ($right > $left && $blankCol($right)) {
            $right--;
        }

        $cut = [$top, $h - 1 - $bottom, $left, $w - 1 - $right];
        if (max($cut) < self::MIN_PX || $top >= $bottom || $left >= $right) {
            return null; // no margin, or the whole picture is white
        }

        $box = [$left, $top, $right - $left + 1, $bottom - $top + 1];

        if ($box[2] < $w * (1 - self::MAX_CUT) || $box[3] < $h * (1 - self::MAX_CUT)) {
            return null; // a small part in a large white field: a cut-out
        }

        // A cut-out part on white keeps its white background (the site shows it whole): only a
        // photo, whose edges are picture once the strip is gone, is trimmed.
        return self::whiteShare($image, $box) < 0.5 ? $box : null;
    }

    /** Share of white pixels along the edges of the box. @param  array{0: int, 1: int, 2: int, 3: int}  $box */
    private static function whiteShare(GdImage $image, array $box): float
    {
        [$x0, $y0, $bw, $bh] = $box;
        $x1 = $x0 + $bw - 1;
        $y1 = $y0 + $bh - 1;
        $white = 0;
        $seen = 0;

        foreach ([[$x0, $y0, 1, 0, $bw], [$x0, $y1, 1, 0, $bw], [$x0, $y0, 0, 1, $bh], [$x1, $y0, 0, 1, $bh]] as [$x, $y, $dx, $dy, $n]) {
            $step = max(1, intdiv($n, 100));
            for ($i = 0; $i < $n; $i += $step) {
                $c = imagecolorsforindex($image, imagecolorat($image, $x + $dx * $i, $y + $dy * $i));
                $seen++;
                if ($c['alpha'] >= 100 || ($c['red'] > self::WHITE && $c['green'] > self::WHITE && $c['blue'] > self::WHITE)) {
                    $white++;
                }
            }
        }

        return $seen ? $white / $seen : 1.0;
    }

    /** Trims the file in place. Returns true when it was changed. */
    public static function file(string $path): bool
    {
        $type = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $image = @imagecreatefromstring((string) @file_get_contents($path));
        if (! $image) {
            return false;
        }

        $box = self::box($image);
        if (! $box) {
            return false;
        }

        $cropped = imagecrop($image, ['x' => $box[0], 'y' => $box[1], 'width' => $box[2], 'height' => $box[3]]);
        if (! $cropped) {
            return false;
        }

        imagesavealpha($cropped, true);

        return match ($type) {
            'webp' => function_exists('imagewebp') && imagewebp($cropped, $path, 85),
            'png' => imagepng($cropped, $path, 6),
            'jpg', 'jpeg' => imagejpeg($cropped, $path, 88),
            default => false,
        };
    }

    /** @param  \Closure(int): array{0: int, 1: int}  $at */
    private static function blank(GdImage $image, int $length, \Closure $at): bool
    {
        $step = max(1, intdiv($length, 400)); // sample long edges, ~400 points are plenty
        $dirty = 0;
        $seen = 0;

        for ($i = 0; $i < $length; $i += $step) {
            [$x, $y] = $at($i);
            $c = imagecolorsforindex($image, imagecolorat($image, $x, $y));
            $seen++;
            if ($c['alpha'] < 100 && ($c['red'] <= self::WHITE || $c['green'] <= self::WHITE || $c['blue'] <= self::WHITE)) {
                $dirty++;
            }
        }

        return $dirty <= $seen * self::NOISE;
    }
}
