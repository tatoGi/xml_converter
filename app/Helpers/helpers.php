<?php

use Illuminate\Support\Facades\Config;


function locales()
{
    $locales = [];

    foreach (Config::get('languages') as $locale) {
        $currentLocale = app()->getLocale();
        $thisLocale = $locale;
        $locales[$locale] = str_replace($currentLocale, $thisLocale, url()->full());
    }

    return $locales;

}

