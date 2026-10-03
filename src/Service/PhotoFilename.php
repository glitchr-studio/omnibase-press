<?php

namespace Base\Press\Service;

use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * The name a downloaded picture is saved under: "<site>-<slug>.<ext>" -
 * "clara-weiss-portrait-with-harp.jpg" -, so the file in a journalist's
 * downloads folder still says whose it is. The slug is the picture's title,
 * else its credit, else its number.
 */
final class PhotoFilename
{
    public static function of(?string $site, ?string $title, ?string $credit, ?int $id, ?string $extension): string
    {
        $slugger = new AsciiSlugger('en');
        $slug = fn (?string $text): string => mb_strtolower($slugger->slug(str_replace(['©', '(c)', '(C)'], ' ', (string) $text))->toString());

        $name = $slug($title) ?: $slug($credit) ?: 'photo'.($id ? '-'.$id : '');
        $site = $slug($site);
        $extension = mb_strtolower(preg_replace('/[^A-Za-z0-9]/', '', (string) $extension));
        // "jpeg" and "jpg" are one thing; the shorter is what people expect.
        $extension = 'jpeg' === $extension ? 'jpg' : $extension;

        return ($site ? $site.'-' : '').$name.($extension ? '.'.$extension : '');
    }
}
