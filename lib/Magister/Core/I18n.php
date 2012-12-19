<?php

/**
 * I18n class.
 * 
 * Handles all internationalisation matters.
 * 
 * @package Magister
 * @subpackage Core
 */
class I18n {

    /**
     * Holds the i18nSource.
     * 
     * @var I18nSource
     */
    public $source;

    /**
     * The default domain.
     * 
     * @var string
     */
    public $defaultDomain = APP;

    /**
     * The instance of the class.
     * 
     * @var I18n
     */
    private static $instance;

    /**
     * I18n constructor.
     */
    private function __construct() {
        $this->source = new GettextI18nSource();
        $this->setLangage();
        $this->loadDomains();
    }

    /**
     * Clone magic function.
     */
    private function __clone() {
        
    }

    /**
     * GetInstance method.
     * 
     * Returns current instance of the I18n object
     * 
     * @return I18n
     */
    public static function getInstance() {
        if (self::$instance == NULL) {
            self::$instance = new I18n();
        }
        return self::$instance;
    }

    /**
     * SetLanguage method.
     * 
     * Sets the application's current language to the specified language. If no 
     * language is specified or the specified language doesn't is not included 
     * in the current app, it defaults to the default language.
     * 
     * @param string $lang
     */
    public function setLangage($lang = null) {
        if (null === $lang) {
            $lang = Config::get('I18n.default');
            if (isset($_SESSION['lang']))
                $lang = $_SESSION['lang'];
        }

        if (!in_array($lang, Config::get('I18n.langs')))
            $lang = Config::get('I18n.default');

        $_SESSION['lang'] = $lang;
        $this->lang = $lang;
        setlocale(LC_ALL, $this->lang);
    }

    /**
     * GetLanguage method.
     * 
     * Returns the current language.
     * 
     * @return string
     */
    public static function getLanguage() {
        return $_SESSION['lang'];
    }

    /**
     * ReadDir method.
     * 
     * Iterates over the specified directory and loads the domains of the 
     * current language.
     * 
     * @param string $dir
     */
    private function readDir($dir) {
        $iterator = new DirectoryIterator($dir);
        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isDot())
                continue;

            if ($fileInfo->isDir() && $fileInfo->getFilename() == $this->lang) {
                $langIterator = new DirectoryIterator($fileInfo->getPathname());
                foreach ($langIterator as $langFileInfo) {
                    if ($langFileInfo->isDot())
                        continue;
                    list(, $extension) = explode('.', $langFileInfo->getBasename());
                    if ($langFileInfo->isFile() && $extension == 'mo')
                        $this->source->addTranslation($langFileInfo->getPathname(), $fileInfo->getBasename('.mo'));
                }
            }

            list(, $extension) = explode('.', $fileInfo->getBasename());
            if ($fileInfo->isFile() && $extension == 'mo' && strpos($fileInfo->getFilename(), '_' . $this->lang . '.mo') !== false)
                $this->source->addTranslation($fileInfo->getPathname(), $fileInfo->getBasename('_' . $this->lang . '.mo'));
        }
    }

    /**
     * LoadDomains method.
     * 
     * Calls I18n::readDir() to load the domains in the app and in the library.
     */
    public function loadDomains() {
        $this->readDir(APP_DIR . DS . 'Locales');
        $this->readDir(LIB_DIR . DS . 'Locales');
    }

    /**
     * DetermineLanguage function.
     * 
     * If the language is specified in the current GET request, updates the 
     * session's language to that one.
     */
    public static function determineLanguage() {
        if (isset($_GET['lang'])) {
            $_SESSION['lang'] = $_GET['lang'];
        }
    }

    /**
     * Translate function.
     * 
     * Translates the given string to the current language. If no domain is 
     * given, default's to the app's domain.
     * 
     * @param string $string
     * @param string $domain
     * @return string
     */
    public static function translate($string, $domain = null) {
        $i18n = self::getInstance();

        if (null === $domain)
            $domain = $i18n->defaultDomain;

        return $i18n->source->translate($string, $domain);
    }

}

/**
 * I18nSource class
 * 
 * Base class for translation sources.
 *
 * @package    Magister
 * @subpackage I18n
 */
abstract class I18nSource {

    /**
     * Translation table.
     * 
     * @var array
     */
    private $translate = array();

    /**
     * Holds information on each domain.
     * 
     * @var array
     */
    private $domainInfo = array();

    /**
     * AddTranslationmethod.
     * 
     * Adds a translation to the table.
     * 
     * @param mixed $location
     * @param string $domain
     * @return boolean
     */
    abstract public function addTranslation($location, $domain);

    /**
     * Translate method.
     * 
     * Translates the given string.
     *
     * @param  string|array $messageId Translation string, or Array for plural translations
     * @param  string $domain The string's domain
     * @return string
     */
    abstract public function translate($messageId, $domain);

    /**
     * Plural method.
     * 
     * Translates the given string using plural notations.
     *
     * @param string $singular
     * @param string $plural
     * @param integer $number
     * @return string
     */
    public function plural($singular, $plural, $number) {
        return $this->translate(array($singular, $plural, $number));
    }

    /**
     * GetPlural method.
     * 
     * Returns the plural definition to use.
     *
     * @param integer $number
     * @param string $locale
     * @return integer
     */
    public static function getPlural($number, $locale) {
        if ($locale == "pt_BR")
        // temporary set a locale for brasilian
            $locale = "xbr";

        if (strlen($locale) > 3)
            $locale = substr($locale, 0, -strlen(strrchr($locale, '_')));

        switch ($locale) {
            case 'bo':
            case 'dz':
            case 'id':
            case 'ja':
            case 'jv':
            case 'ka':
            case 'km':
            case 'kn':
            case 'ko':
            case 'ms':
            case 'th':
            case 'tr':
            case 'vi':
            case 'zh':
                return 0;
                break;
            case 'af':
            case 'az':
            case 'bn':
            case 'bg':
            case 'ca':
            case 'da':
            case 'de':
            case 'el':
            case 'en':
            case 'eo':
            case 'es':
            case 'et':
            case 'eu':
            case 'fa':
            case 'fi':
            case 'fo':
            case 'fur':
            case 'fy':
            case 'gl':
            case 'gu':
            case 'ha':
            case 'he':
            case 'hu':
            case 'is':
            case 'it':
            case 'ku':
            case 'lb':
            case 'ml':
            case 'mn':
            case 'mr':
            case 'nah':
            case 'nb':
            case 'ne':
            case 'nl':
            case 'nn':
            case 'no':
            case 'om':
            case 'or':
            case 'pa':
            case 'pap':
            case 'ps':
            case 'pt':
            case 'so':
            case 'sq':
            case 'sv':
            case 'sw':
            case 'ta':
            case 'te':
            case 'tk':
            case 'ur':
            case 'zu':
                return ($number == 1) ? 0 : 1;
            case 'am':
            case 'bh':
            case 'fil':
            case 'fr':
            case 'gun':
            case 'hi':
            case 'ln':
            case 'mg':
            case 'nso':
            case 'xbr':
            case 'ti':
            case 'wa':
                return (($number == 0) || ($number == 1)) ? 0 : 1;
            case 'be':
            case 'bs':
            case 'hr':
            case 'ru':
            case 'sr':
            case 'uk':
                return (($number % 10 == 1) && ($number % 100 != 11)) ? 0 : ((($number % 10 >= 2) && ($number % 10 <= 4) && (($number % 100 < 10) || ($number % 100 >= 20))) ? 1 : 2);
            case 'cs':
            case 'sk':
                return ($number == 1) ? 0 : ((($number >= 2) && ($number <= 4)) ? 1 : 2);
            case 'ga':
                return ($number == 1) ? 0 : (($number == 2) ? 1 : 2);
            case 'lt':
                return (($number % 10 == 1) && ($number % 100 != 11)) ? 0 : ((($number % 10 >= 2) && (($number % 100 < 10) || ($number % 100 >= 20))) ? 1 : 2);
            case 'sl':
                return ($number % 100 == 1) ? 0 : (($number % 100 == 2) ? 1 : ((($number % 100 == 3) || ($number % 100 == 4)) ? 2 : 3));
            case 'mk':
                return ($number % 10 == 1) ? 0 : 1;
            case 'mt':
                return ($number == 1) ? 0 : ((($number == 0) || (($number % 100 > 1) && ($number % 100 < 11))) ? 1 : ((($number % 100 > 10) && ($number % 100 < 20)) ? 2 : 3));
            case 'lv':
                return ($number == 0) ? 0 : ((($number % 10 == 1) && ($number % 100 != 11)) ? 1 : 2);
            case 'pl':
                return ($number == 1) ? 0 : ((($number % 10 >= 2) && ($number % 10 <= 4) && (($number % 100 < 12) || ($number % 100 > 14))) ? 1 : 2);
            case 'cy':
                return ($number == 1) ? 0 : (($number == 2) ? 1 : ((($number == 8) || ($number == 11)) ? 2 : 3));
            case 'ro':
                return ($number == 1) ? 0 : ((($number == 0) || (($number % 100 > 0) && ($number % 100 < 20))) ? 1 : 2);
            case 'ar':
                return ($number == 0) ? 0 : (($number == 1) ? 1 : (($number == 2) ? 2 : ((($number >= 3) && ($number <= 10)) ? 3 : ((($number >= 11) && ($number <= 99)) ? 4 : 5))));
            default:
                return 0;
        }
    }

}

/**
 * Gettext I18nSouce class.
 *
 * @package    Magister
 * @subpackage I18n
 */
class GettextI18nSource extends I18nSource {

    /**
     * ReadMOData method.
     * 
     * Read values from the MO file.
     *
     * @param resource $file
     * @param string $bytes
     * @param boolean $bigEndian
     */
    private function readMOData($file, $bytes, $bigEndian = false) {
        if ($bigEndian === false)
            return unpack('V' . $bytes, fread($file, 4 * $bytes));
        else
            return unpack('N' . $bytes, fread($file, 4 * $bytes));
    }

    /**
     * AddTranslation method.
     * 
     * Adds a MO file to the translation data.
     *
     * @param string $location MO file to add
     * @param string $domain Domain of the MO file
     * @throws I18nException
     * @return boolean
     */
    public function addTranslation($location, $domain) {
        if (!file_exists($location))
            throw new I18nException('\'' . $location . '\' does not exist');

        $data = array();
        $bigEndian = false;
        $file = fopen($location, 'rb');
        if (filesize($location) < 10) {
            fclose($file);
            throw new I18nException('\'' . $location . '\' is not a gettext file');
        }

        // get Endian
        $input = $this->readMOData($file, 1, $bigEndian);
        if (strtolower(substr(dechex($input[1]), -8)) == "950412de")
            $bigEndian = false;
        elseif (strtolower(substr(dechex($input[1]), -8)) == "de120495")
            $bigEndian = true;
        else {
            fclose($file);
            throw new I18nException('\'' . $location . '\' is not a gettext file');
        }
        // read revision - not supported for now
        $input = $this->readMOData($file, 1, $bigEndian);

        // number of bytes
        $input = $this->readMOData($file, 1, $bigEndian);
        $total = $input[1];

        // number of original strings
        $input = $this->readMOData($file, 1, $bigEndian);
        $OOffset = $input[1];

        // number of translation strings
        $input = $this->readMOData($file, 1, $bigEndian);
        $TOffset = $input[1];

        // fill the original table
        fseek($file, $OOffset);
        $origtemp = $this->readMOData($file, 2 * $total, $bigEndian);
        fseek($file, $TOffset);
        $transtemp = $this->readMOData($file, 2 * $total, $bigEndian);

        for ($count = 0; $count < $total; ++$count) {
            if ($origtemp[$count * 2 + 1] != 0) {
                fseek($file, $origtemp[$count * 2 + 2]);
                $original = @fread($file, $origtemp[$count * 2 + 1]);
                $original = explode("\0", $original);
            } else
                $original[0] = '';

            if ($transtemp[$count * 2 + 1] != 0) {
                fseek($file, $transtemp[$count * 2 + 2]);
                $translate = fread($file, $transtemp[$count * 2 + 1]);
                $translate = explode("\0", $translate);
                if ((count($original) > 1) && (count($translate) > 1)) {
                    $data[$domain][$original[0]] = $translate;
                    array_shift($original);
                    foreach ($original as $orig)
                        $data[$domain][$orig] = '';
                } else
                    $data[$domain][$original[0]] = $translate[0];
            }
        }

        fclose($file);

        $data[$domain][''] = trim($data[$domain]['']);
        if (empty($data[$domain]['']))
            $this->domainInfo[$location] = 'No adapter information available';
        else
            $this->domainInfo[$location] = $data[$domain][''];

        unset($data[$domain]['']);

        if (empty($data)) {
            $data = array();
        }

        $keys = array_keys($data);
        foreach ($keys as $key) {
            if (!isset($this->translate[$key])) {
                $this->translate[$key] = array();
            }

            if (array_key_exists($key, $data) && is_array($data[$key])) {
                $this->translate[$key] = $data[$key] + $this->translate[$key];
            }
        }

        return true;
    }

    /**
     * Translate method.
     * 
     * Translates the given string.
     *
     * @param  string|array $messageId Translation string, or Array for plural translations
     * @param  string $domain The string's domain
     * @return string
     */
    public function translate($messageId, $domain) {
        $plural = null;
        // FIXME: implement plural translations
        if (is_array($messageId)) {
            if (count($messageId) > 2) {
                $number = array_pop($messageId);
                if (!is_numeric($number)) {
                    $plocale = $number;
                    $number = array_pop($messageId);
                } else {
                    $plocale = 'en';
                }

                $plural = $messageId;
                $messageId = $messageId[0];
            } else {
                $messageId = $messageId[0];
            }
        }

        if (!array_key_exists($domain, $this->translate))
            return $messageId;

        if (isset($this->translate[$domain][$messageId])) {
            // return original translation
            if ($plural === null)
                return $this->translate[$domain][$messageId];

            //fix plurals
            /*
              $rule = $this->getPlural($number, $locale);
              if (isset($this->translate[$locale][$plural[0]][$rule]))
              return $this->translate[$locale][$plural[0]][$rule]; */
        } else
            return $messageId;
    }

}