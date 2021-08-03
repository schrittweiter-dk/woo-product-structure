<?php 
unset($argv[0]);
$filename = implode('_-_',$argv);
echo sanitize_file_name($filename);

function sanitize_file_name( $filename ) {
    $filename_raw = $filename;
    $filename     = remove_accents( $filename );
 
    $special_chars = array( '?', '[', ']', '/', '\\', '=', '<', '>', ':', ';', ',', "'", '"', '&', '$', '#', '*', '(', ')', '|', '~', '`', '!', '{', '}', '%', '+', '’', '«', '»', '”', '“', chr( 0 ) );
 
    // Check for support for utf8 in the installed PCRE library once and store the result in a static.
    static $utf8_pcre = null;
    if ( ! isset( $utf8_pcre ) ) {
        // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
        $utf8_pcre = @preg_match( '/^./u', 'a' );
    }
 
    if ( ! seems_utf8( $filename ) ) {
        $_ext     = pathinfo( $filename, PATHINFO_EXTENSION );
        $_name    = pathinfo( $filename, PATHINFO_FILENAME );
        $filename = sanitize_title_with_dashes( $_name ) . '.' . $_ext;
    }
 
    if ( $utf8_pcre ) {
        $filename = preg_replace( "#\x{00a0}#siu", ' ', $filename );
    }
 
    $filename = str_replace( $special_chars, '', $filename );
    $filename = str_replace( array( '%20', '+' ), '-', $filename );
    $filename = preg_replace( '/[\r\n\t -]+/', '-', $filename );
    $filename = trim( $filename, '.-_' );
 
    if ( false === strpos( $filename, '.' ) ) {
        $mime_types = wp_get_mime_types();
        $filetype   = wp_check_filetype( 'test.' . $filename, $mime_types );
        if ( $filetype['ext'] === $filename ) {
            $filename = 'unnamed-file.' . $filetype['ext'];
        }
    }
 
    // Split the filename into a base and extension[s].
    $parts = explode( '.', $filename );
 
    // Return if only one extension.
    if ( count( $parts ) <= 2 ) {
        /** This filter is documented in wp-includes/formatting.php */
       return $filename;
    }
 
    // Process multiple extensions.
    $filename  = array_shift( $parts );
    $extension = array_pop( $parts );
    $mimes     = get_allowed_mime_types();
 
    /*
     * Loop over any intermediate extensions. Postfix them with a trailing underscore
     * if they are a 2 - 5 character long alpha string not in the allowed extension list.
     */
    foreach ( (array) $parts as $part ) {
        $filename .= '.' . $part;
 
        if ( preg_match( '/^[a-zA-Z]{2,5}\d?$/', $part ) ) {
            $allowed = false;
            foreach ( $mimes as $ext_preg => $mime_match ) {
                $ext_preg = '!^(' . $ext_preg . ')$!i';
                if ( preg_match( $ext_preg, $part ) ) {
                    $allowed = true;
                    break;
                }
            }
            if ( ! $allowed ) {
                $filename .= '_';
            }
        }
    }
 
    $filename .= '.' . $extension;
 
    /**
     * Filters a sanitized filename string.
     *
     * @since 2.8.0
     *
     * @param string $filename     Sanitized filename.
     * @param string $filename_raw The filename prior to sanitization.
     */
    return $filename;
}

function remove_accents( $string ) {
    if ( ! preg_match( '/[\x80-\xff]/', $string ) ) {
        return $string;
    }
 
    if ( seems_utf8( $string ) ) {
        $chars = array(
            // Decompositions for Latin-1 Supplement.
            'ª' => 'a',
            'º' => 'o',
            'À' => 'A',
            'Á' => 'A',
            'Â' => 'A',
            'Ã' => 'A',
            'Ä' => 'A',
            'Å' => 'A',
            'Æ' => 'AE',
            'Ç' => 'C',
            'È' => 'E',
            'É' => 'E',
            'Ê' => 'E',
            'Ë' => 'E',
            'Ì' => 'I',
            'Í' => 'I',
            'Î' => 'I',
            'Ï' => 'I',
            'Ð' => 'D',
            'Ñ' => 'N',
            'Ò' => 'O',
            'Ó' => 'O',
            'Ô' => 'O',
            'Õ' => 'O',
            'Ö' => 'O',
            'Ù' => 'U',
            'Ú' => 'U',
            'Û' => 'U',
            'Ü' => 'U',
            'Ý' => 'Y',
            'Þ' => 'TH',
            'ß' => 's',
            'à' => 'a',
            'á' => 'a',
            'â' => 'a',
            'ã' => 'a',
            'ä' => 'a',
            'å' => 'a',
            'æ' => 'ae',
            'ç' => 'c',
            'è' => 'e',
            'é' => 'e',
            'ê' => 'e',
            'ë' => 'e',
            'ì' => 'i',
            'í' => 'i',
            'î' => 'i',
            'ï' => 'i',
            'ð' => 'd',
            'ñ' => 'n',
            'ò' => 'o',
            'ó' => 'o',
            'ô' => 'o',
            'õ' => 'o',
            'ö' => 'o',
            'ø' => 'o',
            'ù' => 'u',
            'ú' => 'u',
            'û' => 'u',
            'ü' => 'u',
            'ý' => 'y',
            'þ' => 'th',
            'ÿ' => 'y',
            'Ø' => 'O',
            // Decompositions for Latin Extended-A.
            'Ā' => 'A',
            'ā' => 'a',
            'Ă' => 'A',
            'ă' => 'a',
            'Ą' => 'A',
            'ą' => 'a',
            'Ć' => 'C',
            'ć' => 'c',
            'Ĉ' => 'C',
            'ĉ' => 'c',
            'Ċ' => 'C',
            'ċ' => 'c',
            'Č' => 'C',
            'č' => 'c',
            'Ď' => 'D',
            'ď' => 'd',
            'Đ' => 'D',
            'đ' => 'd',
            'Ē' => 'E',
            'ē' => 'e',
            'Ĕ' => 'E',
            'ĕ' => 'e',
            'Ė' => 'E',
            'ė' => 'e',
            'Ę' => 'E',
            'ę' => 'e',
            'Ě' => 'E',
            'ě' => 'e',
            'Ĝ' => 'G',
            'ĝ' => 'g',
            'Ğ' => 'G',
            'ğ' => 'g',
            'Ġ' => 'G',
            'ġ' => 'g',
            'Ģ' => 'G',
            'ģ' => 'g',
            'Ĥ' => 'H',
            'ĥ' => 'h',
            'Ħ' => 'H',
            'ħ' => 'h',
            'Ĩ' => 'I',
            'ĩ' => 'i',
            'Ī' => 'I',
            'ī' => 'i',
            'Ĭ' => 'I',
            'ĭ' => 'i',
            'Į' => 'I',
            'į' => 'i',
            'İ' => 'I',
            'ı' => 'i',
            'Ĳ' => 'IJ',
            'ĳ' => 'ij',
            'Ĵ' => 'J',
            'ĵ' => 'j',
            'Ķ' => 'K',
            'ķ' => 'k',
            'ĸ' => 'k',
            'Ĺ' => 'L',
            'ĺ' => 'l',
            'Ļ' => 'L',
            'ļ' => 'l',
            'Ľ' => 'L',
            'ľ' => 'l',
            'Ŀ' => 'L',
            'ŀ' => 'l',
            'Ł' => 'L',
            'ł' => 'l',
            'Ń' => 'N',
            'ń' => 'n',
            'Ņ' => 'N',
            'ņ' => 'n',
            'Ň' => 'N',
            'ň' => 'n',
            'ŉ' => 'n',
            'Ŋ' => 'N',
            'ŋ' => 'n',
            'Ō' => 'O',
            'ō' => 'o',
            'Ŏ' => 'O',
            'ŏ' => 'o',
            'Ő' => 'O',
            'ő' => 'o',
            'Œ' => 'OE',
            'œ' => 'oe',
            'Ŕ' => 'R',
            'ŕ' => 'r',
            'Ŗ' => 'R',
            'ŗ' => 'r',
            'Ř' => 'R',
            'ř' => 'r',
            'Ś' => 'S',
            'ś' => 's',
            'Ŝ' => 'S',
            'ŝ' => 's',
            'Ş' => 'S',
            'ş' => 's',
            'Š' => 'S',
            'š' => 's',
            'Ţ' => 'T',
            'ţ' => 't',
            'Ť' => 'T',
            'ť' => 't',
            'Ŧ' => 'T',
            'ŧ' => 't',
            'Ũ' => 'U',
            'ũ' => 'u',
            'Ū' => 'U',
            'ū' => 'u',
            'Ŭ' => 'U',
            'ŭ' => 'u',
            'Ů' => 'U',
            'ů' => 'u',
            'Ű' => 'U',
            'ű' => 'u',
            'Ų' => 'U',
            'ų' => 'u',
            'Ŵ' => 'W',
            'ŵ' => 'w',
            'Ŷ' => 'Y',
            'ŷ' => 'y',
            'Ÿ' => 'Y',
            'Ź' => 'Z',
            'ź' => 'z',
            'Ż' => 'Z',
            'ż' => 'z',
            'Ž' => 'Z',
            'ž' => 'z',
            'ſ' => 's',
            // Decompositions for Latin Extended-B.
            'Ș' => 'S',
            'ș' => 's',
            'Ț' => 'T',
            'ț' => 't',
            // Euro sign.
            '€' => 'E',
            // GBP (Pound) sign.
            '£' => '',
            // Vowels with diacritic (Vietnamese).
            // Unmarked.
            'Ơ' => 'O',
            'ơ' => 'o',
            'Ư' => 'U',
            'ư' => 'u',
            // Grave accent.
            'Ầ' => 'A',
            'ầ' => 'a',
            'Ằ' => 'A',
            'ằ' => 'a',
            'Ề' => 'E',
            'ề' => 'e',
            'Ồ' => 'O',
            'ồ' => 'o',
            'Ờ' => 'O',
            'ờ' => 'o',
            'Ừ' => 'U',
            'ừ' => 'u',
            'Ỳ' => 'Y',
            'ỳ' => 'y',
            // Hook.
            'Ả' => 'A',
            'ả' => 'a',
            'Ẩ' => 'A',
            'ẩ' => 'a',
            'Ẳ' => 'A',
            'ẳ' => 'a',
            'Ẻ' => 'E',
            'ẻ' => 'e',
            'Ể' => 'E',
            'ể' => 'e',
            'Ỉ' => 'I',
            'ỉ' => 'i',
            'Ỏ' => 'O',
            'ỏ' => 'o',
            'Ổ' => 'O',
            'ổ' => 'o',
            'Ở' => 'O',
            'ở' => 'o',
            'Ủ' => 'U',
            'ủ' => 'u',
            'Ử' => 'U',
            'ử' => 'u',
            'Ỷ' => 'Y',
            'ỷ' => 'y',
            // Tilde.
            'Ẫ' => 'A',
            'ẫ' => 'a',
            'Ẵ' => 'A',
            'ẵ' => 'a',
            'Ẽ' => 'E',
            'ẽ' => 'e',
            'Ễ' => 'E',
            'ễ' => 'e',
            'Ỗ' => 'O',
            'ỗ' => 'o',
            'Ỡ' => 'O',
            'ỡ' => 'o',
            'Ữ' => 'U',
            'ữ' => 'u',
            'Ỹ' => 'Y',
            'ỹ' => 'y',
            // Acute accent.
            'Ấ' => 'A',
            'ấ' => 'a',
            'Ắ' => 'A',
            'ắ' => 'a',
            'Ế' => 'E',
            'ế' => 'e',
            'Ố' => 'O',
            'ố' => 'o',
            'Ớ' => 'O',
            'ớ' => 'o',
            'Ứ' => 'U',
            'ứ' => 'u',
            // Dot below.
            'Ạ' => 'A',
            'ạ' => 'a',
            'Ậ' => 'A',
            'ậ' => 'a',
            'Ặ' => 'A',
            'ặ' => 'a',
            'Ẹ' => 'E',
            'ẹ' => 'e',
            'Ệ' => 'E',
            'ệ' => 'e',
            'Ị' => 'I',
            'ị' => 'i',
            'Ọ' => 'O',
            'ọ' => 'o',
            'Ộ' => 'O',
            'ộ' => 'o',
            'Ợ' => 'O',
            'ợ' => 'o',
            'Ụ' => 'U',
            'ụ' => 'u',
            'Ự' => 'U',
            'ự' => 'u',
            'Ỵ' => 'Y',
            'ỵ' => 'y',
            // Vowels with diacritic (Chinese, Hanyu Pinyin).
            'ɑ' => 'a',
            // Macron.
            'Ǖ' => 'U',
            'ǖ' => 'u',
            // Acute accent.
            'Ǘ' => 'U',
            'ǘ' => 'u',
            // Caron.
            'Ǎ' => 'A',
            'ǎ' => 'a',
            'Ǐ' => 'I',
            'ǐ' => 'i',
            'Ǒ' => 'O',
            'ǒ' => 'o',
            'Ǔ' => 'U',
            'ǔ' => 'u',
            'Ǚ' => 'U',
            'ǚ' => 'u',
            // Grave accent.
            'Ǜ' => 'U',
            'ǜ' => 'u',
        );
 
        // Used for locale-specific rules.
    	$chars['Ä'] = 'Ae';
		$chars['ä'] = 'ae';
		$chars['Ö'] = 'Oe';
		$chars['ö'] = 'oe';
		$chars['Ü'] = 'Ue';
		$chars['ü'] = 'ue';
		$chars['ß'] = 'ss';
	
 
        $string = strtr( $string, $chars );
    } else {
        $chars = array();
        // Assume ISO-8859-1 if not UTF-8.
        $chars['in'] = "\x80\x83\x8a\x8e\x9a\x9e"
            . "\x9f\xa2\xa5\xb5\xc0\xc1\xc2"
            . "\xc3\xc4\xc5\xc7\xc8\xc9\xca"
            . "\xcb\xcc\xcd\xce\xcf\xd1\xd2"
            . "\xd3\xd4\xd5\xd6\xd8\xd9\xda"
            . "\xdb\xdc\xdd\xe0\xe1\xe2\xe3"
            . "\xe4\xe5\xe7\xe8\xe9\xea\xeb"
            . "\xec\xed\xee\xef\xf1\xf2\xf3"
            . "\xf4\xf5\xf6\xf8\xf9\xfa\xfb"
            . "\xfc\xfd\xff";
 
        $chars['out'] = 'EfSZszYcYuAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy';
 
        $string              = strtr( $string, $chars['in'], $chars['out'] );
        $double_chars        = array();
        $double_chars['in']  = array( "\x8c", "\x9c", "\xc6", "\xd0", "\xde", "\xdf", "\xe6", "\xf0", "\xfe" );
        $double_chars['out'] = array( 'OE', 'oe', 'AE', 'DH', 'TH', 'ss', 'ae', 'dh', 'th' );
        $string              = str_replace( $double_chars['in'], $double_chars['out'], $string );
    }
 
    return $string;
}

function sanitize_title_with_dashes( $title, $raw_title = '', $context = 'display' ) {
    $title = strip_tags( $title );
    // Preserve escaped octets.
    $title = preg_replace( '|%([a-fA-F0-9][a-fA-F0-9])|', '---$1---', $title );
    // Remove percent signs that are not part of an octet.
    $title = str_replace( '%', '', $title );
    // Restore octets.
    $title = preg_replace( '|---([a-fA-F0-9][a-fA-F0-9])---|', '%$1', $title );
 
    if ( seems_utf8( $title ) ) {
        if ( function_exists( 'mb_strtolower' ) ) {
            $title = mb_strtolower( $title, 'UTF-8' );
        }
        $title = utf8_uri_encode( $title, 200 );
    }
 
    $title = strtolower( $title );
 
    if ( 'save' === $context ) {
        // Convert &nbsp, &ndash, and &mdash to hyphens.
        $title = str_replace( array( '%c2%a0', '%e2%80%93', '%e2%80%94' ), '-', $title );
        // Convert &nbsp, &ndash, and &mdash HTML entities to hyphens.
        $title = str_replace( array( '&nbsp;', '&#160;', '&ndash;', '&#8211;', '&mdash;', '&#8212;' ), '-', $title );
        // Convert forward slash to hyphen.
        $title = str_replace( '/', '-', $title );
 
        // Strip these characters entirely.
        $title = str_replace(
            array(
                // Soft hyphens.
                '%c2%ad',
                // &iexcl and &iquest.
                '%c2%a1',
                '%c2%bf',
                // Angle quotes.
                '%c2%ab',
                '%c2%bb',
                '%e2%80%b9',
                '%e2%80%ba',
                // Curly quotes.
                '%e2%80%98',
                '%e2%80%99',
                '%e2%80%9c',
                '%e2%80%9d',
                '%e2%80%9a',
                '%e2%80%9b',
                '%e2%80%9e',
                '%e2%80%9f',
                // Bullet.
                '%e2%80%a2',
                // &copy, &reg, &deg, &hellip, and &trade.
                '%c2%a9',
                '%c2%ae',
                '%c2%b0',
                '%e2%80%a6',
                '%e2%84%a2',
                // Acute accents.
                '%c2%b4',
                '%cb%8a',
                '%cc%81',
                '%cd%81',
                // Grave accent, macron, caron.
                '%cc%80',
                '%cc%84',
                '%cc%8c',
            ),
            '',
            $title
        );
 
        // Convert &times to 'x'.
        $title = str_replace( '%c3%97', 'x', $title );
    }
 
    // Kill entities.
    $title = preg_replace( '/&.+?;/', '', $title );
    $title = str_replace( '.', '-', $title );
 
    $title = preg_replace( '/[^%a-z0-9 _-]/', '', $title );
    $title = preg_replace( '/\s+/', '-', $title );
    $title = preg_replace( '|-+|', '-', $title );
    $title = trim( $title, '-' );
 
    return $title;
}

function wp_get_mime_types() {
    /**
     * Filters the list of mime types and file extensions.
     *
     * This filter should be used to add, not remove, mime types. To remove
     * mime types, use the {@see 'upload_mimes'} filter.
     *
     * @since 3.5.0
     *
     * @param string[] $wp_get_mime_types Mime types keyed by the file extension regex
     *                                 corresponding to those types.
     */
    return 
        array(
            // Image formats.
            'jpg|jpeg|jpe'                 => 'image/jpeg',
            'gif'                          => 'image/gif',
            'png'                          => 'image/png',
            'bmp'                          => 'image/bmp',
            'tiff|tif'                     => 'image/tiff',
            'webp'                         => 'image/webp',
            'ico'                          => 'image/x-icon',
            'heic'                         => 'image/heic',
            // Video formats.
            'asf|asx'                      => 'video/x-ms-asf',
            'wmv'                          => 'video/x-ms-wmv',
            'wmx'                          => 'video/x-ms-wmx',
            'wm'                           => 'video/x-ms-wm',
            'avi'                          => 'video/avi',
            'divx'                         => 'video/divx',
            'flv'                          => 'video/x-flv',
            'mov|qt'                       => 'video/quicktime',
            'mpeg|mpg|mpe'                 => 'video/mpeg',
            'mp4|m4v'                      => 'video/mp4',
            'ogv'                          => 'video/ogg',
            'webm'                         => 'video/webm',
            'mkv'                          => 'video/x-matroska',
            '3gp|3gpp'                     => 'video/3gpp',  // Can also be audio.
            '3g2|3gp2'                     => 'video/3gpp2', // Can also be audio.
            // Text formats.
            'txt|asc|c|cc|h|srt'           => 'text/plain',
            'csv'                          => 'text/csv',
            'tsv'                          => 'text/tab-separated-values',
            'ics'                          => 'text/calendar',
            'rtx'                          => 'text/richtext',
            'css'                          => 'text/css',
            'htm|html'                     => 'text/html',
            'vtt'                          => 'text/vtt',
            'dfxp'                         => 'application/ttaf+xml',
            // Audio formats.
            'mp3|m4a|m4b'                  => 'audio/mpeg',
            'aac'                          => 'audio/aac',
            'ra|ram'                       => 'audio/x-realaudio',
            'wav'                          => 'audio/wav',
            'ogg|oga'                      => 'audio/ogg',
            'flac'                         => 'audio/flac',
            'mid|midi'                     => 'audio/midi',
            'wma'                          => 'audio/x-ms-wma',
            'wax'                          => 'audio/x-ms-wax',
            'mka'                          => 'audio/x-matroska',
            // Misc application formats.
            'rtf'                          => 'application/rtf',
            'js'                           => 'application/javascript',
            'pdf'                          => 'application/pdf',
            'swf'                          => 'application/x-shockwave-flash',
            'class'                        => 'application/java',
            'tar'                          => 'application/x-tar',
            'zip'                          => 'application/zip',
            'gz|gzip'                      => 'application/x-gzip',
            'rar'                          => 'application/rar',
            '7z'                           => 'application/x-7z-compressed',
            'exe'                          => 'application/x-msdownload',
            'psd'                          => 'application/octet-stream',
            'xcf'                          => 'application/octet-stream',
            // MS Office formats.
            'doc'                          => 'application/msword',
            'pot|pps|ppt'                  => 'application/vnd.ms-powerpoint',
            'wri'                          => 'application/vnd.ms-write',
            'xla|xls|xlt|xlw'              => 'application/vnd.ms-excel',
            'mdb'                          => 'application/vnd.ms-access',
            'mpp'                          => 'application/vnd.ms-project',
            'docx'                         => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'docm'                         => 'application/vnd.ms-word.document.macroEnabled.12',
            'dotx'                         => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
            'dotm'                         => 'application/vnd.ms-word.template.macroEnabled.12',
            'xlsx'                         => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xlsm'                         => 'application/vnd.ms-excel.sheet.macroEnabled.12',
            'xlsb'                         => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
            'xltx'                         => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
            'xltm'                         => 'application/vnd.ms-excel.template.macroEnabled.12',
            'xlam'                         => 'application/vnd.ms-excel.addin.macroEnabled.12',
            'pptx'                         => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'pptm'                         => 'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
            'ppsx'                         => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
            'ppsm'                         => 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
            'potx'                         => 'application/vnd.openxmlformats-officedocument.presentationml.template',
            'potm'                         => 'application/vnd.ms-powerpoint.template.macroEnabled.12',
            'ppam'                         => 'application/vnd.ms-powerpoint.addin.macroEnabled.12',
            'sldx'                         => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
            'sldm'                         => 'application/vnd.ms-powerpoint.slide.macroEnabled.12',
            'onetoc|onetoc2|onetmp|onepkg' => 'application/onenote',
            'oxps'                         => 'application/oxps',
            'xps'                          => 'application/vnd.ms-xpsdocument',
            // OpenOffice formats.
            'odt'                          => 'application/vnd.oasis.opendocument.text',
            'odp'                          => 'application/vnd.oasis.opendocument.presentation',
            'ods'                          => 'application/vnd.oasis.opendocument.spreadsheet',
            'odg'                          => 'application/vnd.oasis.opendocument.graphics',
            'odc'                          => 'application/vnd.oasis.opendocument.chart',
            'odb'                          => 'application/vnd.oasis.opendocument.database',
            'odf'                          => 'application/vnd.oasis.opendocument.formula',
            // WordPerfect formats.
            'wp|wpd'                       => 'application/wordperfect',
            // iWork formats.
            'key'                          => 'application/vnd.apple.keynote',
            'numbers'                      => 'application/vnd.apple.numbers',
            'pages'                        => 'application/vnd.apple.pages',
        );
}

function wp_check_filetype( $filename, $mimes = null ) {
    if ( empty( $mimes ) ) {
        $mimes = get_allowed_mime_types();
    }
    $type = false;
    $ext  = false;
 
    foreach ( $mimes as $ext_preg => $mime_match ) {
        $ext_preg = '!\.(' . $ext_preg . ')$!i';
        if ( preg_match( $ext_preg, $filename, $ext_matches ) ) {
            $type = $mime_match;
            $ext  = $ext_matches[1];
            break;
        }
    }
 
    return compact( 'ext', 'type' );
}

function get_allowed_mime_types( $user = null ) {
    $t = wp_get_mime_types();
 
    unset( $t['swf'], $t['exe'] );

    if ( empty( $unfiltered ) ) {
        unset( $t['htm|html'], $t['js'] );
    }
 
    /**
     * Filters list of allowed mime types and file extensions.
     *
     * @since 2.0.0
     *
     * @param array            $t    Mime types keyed by the file extension regex corresponding to those types.
     * @param int|WP_User|null $user User ID, User object or null if not provided (indicates current user).
     */
    return $t;
}

function seems_utf8( $str ) {
    mbstring_binary_safe_encoding();
    $length = strlen( $str );
    reset_mbstring_encoding();
    for ( $i = 0; $i < $length; $i++ ) {
        $c = ord( $str[ $i ] );
        if ( $c < 0x80 ) {
            $n = 0; // 0bbbbbbb
        } elseif ( ( $c & 0xE0 ) == 0xC0 ) {
            $n = 1; // 110bbbbb
        } elseif ( ( $c & 0xF0 ) == 0xE0 ) {
            $n = 2; // 1110bbbb
        } elseif ( ( $c & 0xF8 ) == 0xF0 ) {
            $n = 3; // 11110bbb
        } elseif ( ( $c & 0xFC ) == 0xF8 ) {
            $n = 4; // 111110bb
        } elseif ( ( $c & 0xFE ) == 0xFC ) {
            $n = 5; // 1111110b
        } else {
            return false; // Does not match any model.
        }
        for ( $j = 0; $j < $n; $j++ ) { // n bytes matching 10bbbbbb follow ?
            if ( ( ++$i == $length ) || ( ( ord( $str[ $i ] ) & 0xC0 ) != 0x80 ) ) {
                return false;
            }
        }
    }
    return true;
}

function mbstring_binary_safe_encoding( $reset = false ) {
    static $encodings  = array();
    static $overloaded = null;
 
    if ( is_null( $overloaded ) ) {
        if ( function_exists( 'mb_internal_encoding' )
            && ( (int) ini_get( 'mbstring.func_overload' ) & 2 ) // phpcs:ignore PHPCompatibility.IniDirectives.RemovedIniDirectives.mbstring_func_overloadDeprecated
        ) {
            $overloaded = true;
        } else {
            $overloaded = false;
        }
    }
 
    if ( false === $overloaded ) {
        return;
    }
 
    if ( ! $reset ) {
        $encoding = mb_internal_encoding();
        array_push( $encodings, $encoding );
        mb_internal_encoding( 'ISO-8859-1' );
    }
 
    if ( $reset && $encodings ) {
        $encoding = array_pop( $encodings );
        mb_internal_encoding( $encoding );
    }
}

function reset_mbstring_encoding() {
    mbstring_binary_safe_encoding( true );
}

function utf8_uri_encode( $utf8_string, $length = 0 ) {
    $unicode        = '';
    $values         = array();
    $num_octets     = 1;
    $unicode_length = 0;
 
    mbstring_binary_safe_encoding();
    $string_length = strlen( $utf8_string );
    reset_mbstring_encoding();
 
    for ( $i = 0; $i < $string_length; $i++ ) {
 
        $value = ord( $utf8_string[ $i ] );
 
        if ( $value < 128 ) {
            if ( $length && ( $unicode_length >= $length ) ) {
                break;
            }
            $unicode .= chr( $value );
            $unicode_length++;
        } else {
            if ( count( $values ) == 0 ) {
                if ( $value < 224 ) {
                    $num_octets = 2;
                } elseif ( $value < 240 ) {
                    $num_octets = 3;
                } else {
                    $num_octets = 4;
                }
            }
 
            $values[] = $value;
 
            if ( $length && ( $unicode_length + ( $num_octets * 3 ) ) > $length ) {
                break;
            }
            if ( count( $values ) == $num_octets ) {
                for ( $j = 0; $j < $num_octets; $j++ ) {
                    $unicode .= '%' . dechex( $values[ $j ] );
                }
 
                $unicode_length += $num_octets * 3;
 
                $values     = array();
                $num_octets = 1;
            }
        }
    }
 
    return $unicode;
}