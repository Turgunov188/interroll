<?php

namespace Tests\Unit;

use App\Support\ImageTrim;
use PHPUnit\Framework\TestCase;

final class ImageTrimTest extends TestCase
{
    public function test_a_photo_loses_the_white_strip_on_its_side(): void
    {
        $image = imagecreatetruecolor(400, 300);
        imagefill($image, 0, 0, imagecolorallocate($image, 255, 255, 255));
        imagefilledrectangle($image, 0, 0, 379, 299, imagecolorallocate($image, 200, 60, 40)); // photo, 20 px strip on the right

        $this->assertSame([0, 0, 380, 300], ImageTrim::box($image));
    }

    public function test_a_part_on_white_is_left_whole(): void
    {
        $image = imagecreatetruecolor(400, 300);
        imagefill($image, 0, 0, imagecolorallocate($image, 255, 255, 255));
        imagefilledrectangle($image, 40, 140, 360, 160, imagecolorallocate($image, 60, 60, 60)); // a thin bar

        $this->assertNull(ImageTrim::box($image));
    }

    public function test_a_photo_without_margins_is_untouched(): void
    {
        $image = imagecreatetruecolor(400, 300);
        imagefill($image, 0, 0, imagecolorallocate($image, 30, 90, 160));

        $this->assertNull(ImageTrim::box($image));
    }
}
