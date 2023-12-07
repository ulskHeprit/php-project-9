<?php

namespace Hexlet\Code\Validators;

use DateTime;
use Hexlet\Code\Models\Url;

/**
 * UrlValidator
 */
class UrlValidator
{
    private static $dateFormat = 'Y-m-d H:i:s';

    /**
     * @param Url $url
     * @return array
     */
    public static function validate(Url $url)
    {
        $errors = [];

        if (empty($url->getName())) {
            $errors['name'] = 'Пустой url';
        }

        if (mb_strlen($url->getName()) > 255) {
            $errors['name'] = 'Длинный url (>255)';
        }

        if (get_headers($url->getName()) === false) {
            $errors['name'] = 'Некорректный url';
        }

        if (!static::validateDateTime($url->getCreatedAt())) {
            $errors['created_at'] = 'Непраавильный формат создания (yyyy-mm-dd hh:mm:ss)';
        }

        return $errors;
    }

    /**
     * @param $date
     * @return bool
     */
    private static function validateDateTime($date)
    {
        date_default_timezone_set('UTC');
        $d = DateTime::createFromFormat(static::$dateFormat, $date);
        return $d && ($d->format(static::$dateFormat) === $date);
    }
}
