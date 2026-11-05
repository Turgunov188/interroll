<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * Name for a stored upload: random, with an extension from an allow-list.
 *
 * Laravel's hashName() picks the extension from the file's content, so an HTML page named
 * «drawing.pdf» would be stored as .html and run its scripts when a manager opens the link.
 * Here the extension is the one the user gave, and only if it is on the list; anything else
 * becomes .bin, which browsers download instead of opening.
 */
final class UploadName
{
    /** @param  list<string>  $allowed  lower-case extensions */
    public static function for(UploadedFile $file, array $allowed): string
    {
        return Str::random(40).'.'.self::extension($file->getClientOriginalName(), $allowed);
    }

    /** @param  list<string>  $allowed */
    public static function extension(string $name, array $allowed): string
    {
        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));

        return in_array($extension, $allowed, true) ? $extension : 'bin';
    }
}
