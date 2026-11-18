<?php

namespace App\Support;

/**
 * Fixed codes for the Sangh form: प्रादेशिक विभाग / जिल्हा short codes (used in
 * the Pradeshik / District reference numbers, e.g. "MM/12"), and श्रेणी / संघ
 * प्रकार labels. One list for the form, the numbering and the screens.
 */
class SanghCodes
{
    /** प्रादेशिक विभाग => code, in form order. */
    public const VIBHAGS = [
        'अहिल्यानगर' => 'AN',
        'बुलढाणा' => 'BD',
        'पूर्व.विदर्भ' => 'EV',
        'खानदेश' => 'KD',
        'कोकण' => 'KK',
        'कोल्हापूर' => 'KR',
        'मुंबई' => 'MM',
        'नाशिक' => 'NS',
        'उत्तर.मराठवाडा' => 'UM',
        'पुणे' => 'PN',
        'सांगली' => 'SG',
        'दक्षिण.मराठवाडा' => 'SM',
        'वनवैभव' => 'VV',
        'पश्चिम.विदर्भ' => 'WV',
    ];

    /** जिल्हा => code, in form order. */
    public const DISTRICTS = [
        'अकोला' => 'AK',
        'अमरावती' => 'AM',
        'अहिल्यानगर' => 'AN',
        'बीड' => 'BD',
        'भंडारा' => 'BH',
        'बुलढाणा' => 'BL',
        'चंद्रपूर' => 'CP',
        'धाराशिव' => 'DS',
        'धुळे' => 'DH',
        'गडचिरोली' => 'GC',
        'गोंदिया' => 'GY',
        'हिंगोली' => 'HG',
        'जळगाव' => 'JG',
        'जालना' => 'JL',
        'कोल्हापूर' => 'KR',
        'लातूर' => 'LT',
        'मुंबई सिटी' => 'MC',
        'मुंबई इतर' => 'MO',
        'नागपूर' => 'NG',
        'नांदेड' => 'ND',
        'नंदुरबार' => 'NB',
        'नाशिक' => 'NS',
        'पालघर' => 'PL',
        'परभणी' => 'PB',
        'पुणे' => 'PN',
        'रायगड' => 'RG',
        'रत्नागिरी' => 'RT',
        'सांगली' => 'SG',
        'सिंधुदुर्ग' => 'SD',
        'सोलापूर' => 'SL',
        'संभाजी नगर' => 'SN',
        'सातारा' => 'ST',
        'ठाणे' => 'TH',
        'वर्धा' => 'VR',
        'वाशीम' => 'VS',
        'यवतमाळ' => 'YM',
    ];

    /** श्रेणी code => label. */
    public const CATEGORIES = [
        'R' => 'ग्रामीण',
        'U' => 'शहरी',
        'A' => 'आदिवासी',
    ];

    /** संघ प्रकार code => label. */
    public const TYPES = [
        'G' => 'सर्वसाधारण',
        'F' => 'महिला',
    ];

    public static function vibhag(?string $name): ?string
    {
        return self::VIBHAGS[trim((string) $name)] ?? null;
    }

    public static function district(?string $name): ?string
    {
        return self::DISTRICTS[trim((string) $name)] ?? null;
    }

    /** "आदिवासी - A" style label, or the raw value when unknown. */
    public static function categoryLabel(?string $code): ?string
    {
        return isset(self::CATEGORIES[$code]) ? self::CATEGORIES[$code] . ' - ' . $code : $code;
    }

    public static function typeLabel(?string $code): ?string
    {
        return isset(self::TYPES[$code]) ? self::TYPES[$code] . ' - ' . $code : $code;
    }
}
