<?php
/* -----------------------------------------------------------
Copyright (c) 2019 Releva GmbH - https://www.releva.nz
Released under the MIT License (Expat)
[https://opensource.org/licenses/MIT]
--------------------------------------------------------------
*/
namespace Releva\Retargeting\Base;

/**
 * Helper Class to convert strings to UTF-8
 */
class Utf8Util {
    const CHARSET_ORDER = [
        // The order is just wild guesswork.
        // First almost all ISO encondings.
        'ISO-8859-1', 'ISO-8859-2', 'ISO-8859-3', 'ISO-8859-4', 'ISO-8859-5', 'ISO-8859-6', 'ISO-8859-7',
        'ISO-8859-8', 'ISO-8859-9', 'ISO-8859-10', 'ISO-8859-13', 'ISO-8859-14', 'ISO-8859-15', 'ISO-8859-16',
        'ISO-2022-KR', 'ISO-2022-JP', 'ISO-2022-JP-MS',

        // The old dos ones.
        'Windows-1252', 'Windows-1254', 'Windows-1251',

        'BASE64', 'UUENCODE', 'HTML-ENTITIES', 'Quoted-Printable', '7bit', '8bit', 'pass', 'wchar',
        'byte2be', 'byte2le', 'byte4be', 'byte4le',
        'ASCII', 'ArmSCII-8',

        'CP932', 'CP51932', 'CP936', 'CP950', 'CP850', 'CP50220', 'CP50220raw', 'CP50221', 'CP50222', 'CP866',

        'UCS-4', 'UCS-4BE', 'UCS-4LE', 'UCS-2', 'UCS-2BE', 'UCS-2LE', 'UTF-32', 'UTF-32BE', 'UTF-32LE',
        'UTF-16', 'UTF-16BE', 'UTF-16LE', 'UTF-7', 'UTF7-IMAP', 'UTF-8',
        'UTF-8-Mobile#DOCOMO', 'UTF-8-Mobile#KDDI-A', 'UTF-8-Mobile#KDDI-B', 'UTF-8-Mobile#SOFTBANK',

        // The japanese stuff here, because some may get confused with the ISO ones.
        'EUC-JP', 'SJIS', 'eucJP-win', 'EUC-JP-2004', 'SJIS-win', 'SJIS-Mobile#DOCOMO', 'SJIS-Mobile#KDDI',
        'SJIS-Mobile#SOFTBANK', 'SJIS-mac', 'SJIS-2004',

        // No idea.
        'JIS', 'GB18030', 'EUC-CN', 'HZ', 'EUC-TW', 'BIG-5', 'EUC-KR', 'UHC', 'KOI8-R', 'KOI8-U', 'JIS-ms',
        'ISO-2022-JP-2004', 'ISO-2022-JP-MOBILE#KDDI'
    ];

    protected static $charsetOrder = [];
    protected static $supportedCharsetsLower = [];

    protected function __contruct() {
    }

    protected static function getCharsetDetectOrder() {
        if (empty(self::$charsetOrder)) {
            $sE = mb_list_encodings();
            self::$charsetOrder = array_merge(
                array_intersect(self::CHARSET_ORDER, $sE), // remove not supported encodings
                array_diff($sE, self::CHARSET_ORDER) // add missing encodings to the end of the array.
            );
            self::$supportedCharsetsLower = array_map(function ($m) {
                return strtolower($m);
            }, self::$charsetOrder);
        }
        return self::$charsetOrder;
    }


    protected static function legacyIsUtf8($str) {
        $len = strlen($str);
        for($i = 0; $i < $len; ++$i){
            $c = ord($str[$i]);
            if ($c > 128) {
                if (($c > 247)) return false;
                elseif ($c > 239) $bytes = 4;
                elseif ($c > 223) $bytes = 3;
                elseif ($c > 191) $bytes = 2;
                else return false;
                if (($i + $bytes) > $len) return false;
                while ($bytes > 1) {
                    ++$i;
                    $b = ord($str[$i]);
                    if ($b < 128 || $b > 191) return false;
                    --$bytes;
                }
            }
        }
        return true;
    }

    public static function toUtf8($str, $charset = 'default') {
        if (!is_string($str)) {
            return $str;
        }
        // If the string is already valid UTF-8, return it immediately.
        // mb_detect_encoding with ISO-8859-1 first in the order would otherwise
        // misidentify valid UTF-8 bytes (e.g. German umlauts) as ISO-8859-1
        // and double-encode them.
        if (function_exists('mb_check_encoding') && mb_check_encoding($str, 'UTF-8')) {
            return $str;
        }
        // Check dependencies
        if (!function_exists('mb_detect_encoding') || !function_exists('iconv')) {
            // Fall back to a flawed but in most cases working native implementation.
            if (self::legacyIsUtf8($str)) {
                return $str;
            }
            return utf8_encode($str);
        }

        $charset = strtolower(empty($charset) ? '' : $charset);
        $actualCharset = $charset;
        $detectedCharset = strtolower((string)mb_detect_encoding($str, self::getCharsetDetectOrder()));

        if (
            (empty($charset) || ($charset === 'default') || ($charset === 'us-ascii') || !in_array($charset, self::$supportedCharsetsLower))
            && !empty($detectedCharset)
            && ($charset !== $detectedCharset)
        ) {
            $actualCharset = $detectedCharset;
        } elseif (
            // Someone is lying. Figure out if it is mb_detect_encoding, which thinks that utf-8 encoded string
            // are also valid ISO-8859-1 or if the mail is lying.
            ($charset === 'utf-8')
            && ($detectedCharset !== 'utf-8')
            && !mb_check_encoding($str, 'UTF-8')
            && preg_match('//u', $str) !== 1 // this here seems to be the most reliable.
        ) {
            $actualCharset = $detectedCharset;
        }

        if (!empty($actualCharset) && ($actualCharset !== 'utf-8')) {
            $str = iconv($actualCharset, 'UTF-8//TRANSLIT//IGNORE', $str);
        }
        return $str;
    }

}
