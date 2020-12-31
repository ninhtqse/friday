<?php
/**
 * ベンチャー・リンク　すらら
 *
 * JSONツール
 *
 * @author Azet
 */

// credit http://blogs.coldbuffer.com/json_encode-for-php-4

if (!function_exists("json_encode")) {

	/**
	 * PHPオブジェクトはJSONに変換
	 *
	 * AC:[C]共通 UC1:[C99]その他.
	 *
	 * @author Azet
	 * @param mixed $a = false
	 * @return string
	 */
    function json_encode($a = false) {
        if (is_null($a))
            return 'null';
        if ($a === false)
            return 'false';
        if ($a === true)
            return 'true';
        if (is_scalar($a)) {
            if (is_float($a)) {
                // Always use "." for floats.
                return floatval(str_replace(",", ".", strval($a)));
            }

            if (is_string($a)) {
                static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
                return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
            } else
                return $a;
        }
        $isList = true;
        for ($i = 0, reset($a); $i < count($a); $i++, next($a)) {
            if (key($a) !== $i) {
                $isList = false;
                break;
            }
        } $result = array();
        if ($isList) {
            foreach ($a as $v)
                $result[] = json_encode($v);
            return '[' . join(',', $result) . ']';
        } else {
            foreach ($a as $k => $v)
                $result[] = json_encode($k) . ':' . json_encode($v);
            return '{' . join(',', $result) . '}';
        }
    }
    //end function json_encode
}
