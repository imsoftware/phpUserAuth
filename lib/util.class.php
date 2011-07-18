<?
class Util 
{
	public static function html2text ( $page ) {
		$search = array("'<script[^>]*?>.*?</script>'si",	// strip out javascript
				"'<[\/\!]*?[^<>]*?>'si",		// strip out html tags
				"'([\r\n])[\s]+'",			// strip out white space
				"'@<![\s\S]*?ñ[ \t\n\r]*>@'",
				"'&(quot|#34|#034|#x22);'i",		// replace html entities
				"'&(amp|#38|#038|#x26);'i",		// added hexadecimal values
				"'&(lt|#60|#060|#x3c);'i",
				"'&(gt|#62|#062|#x3e);'i",
				"'&(nbsp|#160|#xa0);'i",
				"'&(iexcl|#161);'i",
				"'&(cent|#162);'i",
				"'&(pound|#163);'i",
				"'&(copy|#169);'i",
				"'&(reg|#174);'i",
				"'&(deg|#176);'i",
				"'&(#39|#039|#x27);'",
				"'&(euro|#8364);'i",			// europe
				"'&a(uml|UML);'",			// german
				"'&o(uml|UML);'",
				"'&u(uml|UML);'",
				"'&A(uml|UML);'",
				"'&O(uml|UML);'",
				"'&U(uml|UML);'",
				"'&szlig;'i",
				);
		$replace = array(	"",
					"",
					" ",
					"\"",
					"&",
					"<",
					">",
					" ",
					chr(161),
					chr(162),
					chr(163),
					chr(169),
					chr(174),
					chr(176),
					chr(39),
					chr(128),
					"‰",
					"ˆ",
					"¸",
					"ƒ",
					"÷",
					"‹",
					"ﬂ",
				);

		$text = preg_replace($search,$replace,$page);
		return trim ( $text );
	}
}
?>