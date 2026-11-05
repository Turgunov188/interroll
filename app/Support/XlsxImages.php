<?php

namespace App\Support;

use RuntimeException;
use ZipArchive;

/**
 * Puts small pictures into the cells of an .xlsx written by OpenSpout (which writes no images).
 * Adds the standard parts by hand: xl/media/*, a drawing with one anchor per picture and its relations.
 *
 *   XlsxImages::embed($path, [1 => '/path/a.webp', 2 => '/path/b.jpg'], column: 0, size: 64);
 *
 * Rows and columns are 0-based (row 0 is the header). Pictures are turned into JPEG thumbnails on
 * a white square, so WebP / PNG with transparency show the same in every Excel version.
 */
final class XlsxImages
{
    private const EMU_PER_PIXEL = 9525;

    private const NS_REL = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';

    /** @param  array<int, string>  $images  row => image file */
    public static function embed(string $xlsx, array $images, int $column = 0, int $size = 64, int $offset = 4): int
    {
        $thumbs = [];
        foreach ($images as $row => $file) {
            if (($jpeg = self::thumbnail($file, $size * 2)) !== null) {
                $thumbs[$row] = $jpeg;
            }
        }

        if ($thumbs === []) {
            return 0;
        }

        $zip = new ZipArchive;
        if ($zip->open($xlsx) !== true) {
            throw new RuntimeException("Cannot open {$xlsx}");
        }

        $anchors = '';
        $relations = '';
        $n = 0;
        foreach ($thumbs as $row => $jpeg) {
            $n++;
            $zip->addFromString("xl/media/image{$n}.jpeg", $jpeg);
            $relations .= '<Relationship Id="rId'.$n.'" Type="'.self::NS_REL.'/image" Target="../media/image'.$n.'.jpeg"/>';
            $anchors .= self::anchor($n, $row, $column, $size * self::EMU_PER_PIXEL, $offset * self::EMU_PER_PIXEL);
        }

        $zip->addFromString('xl/drawings/drawing1.xml',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<xdr:wsDr xmlns:xdr="http://schemas.openxmlformats.org/drawingml/2006/spreadsheetDrawing" xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:r="'.self::NS_REL.'">'
            .$anchors.'</xdr:wsDr>');
        $zip->addFromString('xl/drawings/_rels/drawing1.xml.rels',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'.$relations.'</Relationships>');

        // the sheet points at the drawing (before <legacyDrawing>, as the schema orders them)
        $sheet = (string) $zip->getFromName('xl/worksheets/sheet1.xml');
        $tag = '<drawing r:id="rIdPictures"/>';
        $sheet = str_contains($sheet, '<legacyDrawing')
            ? preg_replace('/<legacyDrawing\b/', $tag.'<legacyDrawing', $sheet, 1)
            : str_replace('</worksheet>', $tag.'</worksheet>', $sheet);
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheet);

        $rels = $zip->getFromName('xl/worksheets/_rels/sheet1.xml.rels')
            ?: '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"></Relationships>';
        $zip->addFromString('xl/worksheets/_rels/sheet1.xml.rels', str_replace('</Relationships>',
            '<Relationship Id="rIdPictures" Type="'.self::NS_REL.'/drawing" Target="../drawings/drawing1.xml"/></Relationships>', $rels));

        $types = (string) $zip->getFromName('[Content_Types].xml');
        if (! str_contains($types, 'Extension="jpeg"')) {
            $types = str_replace('</Types>', '<Default ContentType="image/jpeg" Extension="jpeg"/></Types>', $types);
        }
        $types = str_replace('</Types>', '<Override ContentType="application/vnd.openxmlformats-officedocument.drawing+xml" PartName="/xl/drawings/drawing1.xml"/></Types>', $types);
        $zip->addFromString('[Content_Types].xml', $types);

        $zip->close();

        return $n;
    }

    private static function anchor(int $n, int $row, int $column, int $size, int $offset): string
    {
        return '<xdr:oneCellAnchor>'
            ."<xdr:from><xdr:col>{$column}</xdr:col><xdr:colOff>{$offset}</xdr:colOff><xdr:row>{$row}</xdr:row><xdr:rowOff>{$offset}</xdr:rowOff></xdr:from>"
            ."<xdr:ext cx=\"{$size}\" cy=\"{$size}\"/>"
            .'<xdr:pic>'
            ."<xdr:nvPicPr><xdr:cNvPr id=\"{$n}\" name=\"Picture {$n}\"/><xdr:cNvPicPr><a:picLocks noChangeAspect=\"1\"/></xdr:cNvPicPr></xdr:nvPicPr>"
            ."<xdr:blipFill><a:blip r:embed=\"rId{$n}\"/><a:stretch><a:fillRect/></a:stretch></xdr:blipFill>"
            ."<xdr:spPr><a:xfrm><a:off x=\"0\" y=\"0\"/><a:ext cx=\"{$size}\" cy=\"{$size}\"/></a:xfrm><a:prstGeom prst=\"rect\"><a:avLst/></a:prstGeom></xdr:spPr>"
            .'</xdr:pic><xdr:clientData/></xdr:oneCellAnchor>';
    }

    /** Square JPEG with the picture fitted inside, or null when the file cannot be read (or GD is missing). */
    private static function thumbnail(string $file, int $side): ?string
    {
        if (! function_exists('imagecreatefromstring') || ! is_file($file)) {
            return null;
        }

        $source = @imagecreatefromstring((string) file_get_contents($file));
        if ($source === false) {
            return null;
        }

        [$w, $h] = [imagesx($source), imagesy($source)];
        $scale = min($side / $w, $side / $h, 1.0);
        [$tw, $th] = [max(1, (int) round($w * $scale)), max(1, (int) round($h * $scale))];

        $canvas = imagecreatetruecolor($side, $side);
        imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
        imagecopyresampled($canvas, $source, intdiv($side - $tw, 2), intdiv($side - $th, 2), 0, 0, $tw, $th, $w, $h);

        ob_start();
        imagejpeg($canvas, null, 82);

        return (string) ob_get_clean();
    }
}
