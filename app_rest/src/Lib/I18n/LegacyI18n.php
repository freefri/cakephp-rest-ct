<?php

namespace App\Lib\I18n;

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Http\Exception\BadRequestException;
use Cake\I18n\Formatter\IcuFormatter;
use Cake\I18n\Formatter\SprintfFormatter;
use Cake\I18n\FormatterLocator;
use Cake\I18n\I18n;
use Cake\I18n\PackageLocator;
use Cake\I18n\Translator;
use Cake\I18n\TranslatorRegistry;
use Cake\Utility\Inflector;
use L;

class LegacyI18n extends I18n
{
    public static function translators(): TranslatorRegistry
    {
        if (static::$_collection !== null) {
            return static::$_collection;
        }

        static::$_collection = new LegacyTranslatorRegistry( // start loading custom legacy classes
            new PackageLocator(),
            new FormatterLocator([
                'default' => IcuFormatter::class,
                'sprintf' => SprintfFormatter::class,
            ]),
            static::getLocale()
        );

        if (class_exists(Cache::class)) {
            static::$_collection->setCacher(Cache::pool('_cake_core_'));
        }

        return static::$_collection;
    }

    public static function getPluginLocaleDir(): string
    {
        return 'fakePlugin';
    }

    private static function getDefaultName($name): string
    {
        //if ($name === 'default') {
        //    if (is_dir(self::getPluginLocaleDir())) {
        //        return Inflector::underscore(getPluginNameWithCt());
        //    }
        //}
        return $name;
    }

    public static function getTranslator(string $name = 'default', ?string $locale = null): Translator
    {
        $name = self::getDefaultName($name);
        return parent::getTranslator($name, $locale);
    }

    public static function getLocaleUrl(): string
    {
        return Configure::read('i18n.languages.' . I18n::getLocale() . '.url');
    }

    public static function getLocaleIntl(): string
    {
        return Configure::read('i18n.languages.' . I18n::getLocale() . '.intl');
    }

    public static function setLocale(string $locale): void
    {
        parent::setLocale($locale);
    }

    public static function isDefaultRtl(): string
    {
        $defaultLang = LegacyI18n::convertTo3Letter(Configure::read('Company.lang_url'));
        return Configure::read('i18n.languages.' . $defaultLang . '.dir') == 'rtl';
    }

    public static function convertTo3Letter($lang2letter): string
    {
        $languages = Configure::read('i18n.languages');
        if (!$languages) {
            throw new BadRequestException('Languages cannot be loaded from Configure');
        }
        foreach ($languages as $iso3letter => $lang) {
            if ($lang['url'] == $lang2letter) {
                return $iso3letter;
            }
        }
        throw new BadRequestException('Language (2 letter) does not exist ' . $lang2letter);
    }
}
