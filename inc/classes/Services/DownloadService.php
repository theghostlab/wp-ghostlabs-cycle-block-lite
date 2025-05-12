<?php

namespace THEGHOSTLAB\CYCLE\Services;

class DownloadService
{
    public function setCsv(string $filename = 'theghostlab-utm-sets'): string
    {
        $siteSlug = sanitize_title(get_bloginfo('name'));
        $date = (new \DateTimeImmutable())->format('Y-m-d-His');

        return sprintf("%s-%s-%s.csv", $siteSlug, $filename, $date);
    }

    public function downloadData(array $selected)
    {

    }
}