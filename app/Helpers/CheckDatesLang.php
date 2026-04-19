<?php


namespace App\Helpers;

class CheckDatesLang  {
   public static $arabicDays = [
        'الاحد' => 'Sunday',
        'الاثنين' => 'Monday',
        'الثلاثاء' => 'Tuesday',
        'الاربعاء' => 'Wednesday',
        'الخميس' => 'Thursday',
        'الجمعة' => 'Friday',
        'السبت' => 'Saturday'
    ];

    public static function checkLanguage($string) {
        // Regular expression for Arabic letters
        $arabicPattern = '/\p{Arabic}/u';
        // Regular expression for English letters
        $englishPattern = '/[a-zA-Z]/';
    
        if (preg_match($arabicPattern, $string)) {
            return 'ar';
        } elseif (preg_match($englishPattern, $string)) {
            return 'en';
        } else {
            return '';
        }
    } 

    public static function getEnglishDay ($day) {
        if(self::checkLanguage($day) == 'ar') {
            return strtolower(self::$arabicDays[$day]);
        } else {
            return strtolower($day);
        }
    }
}