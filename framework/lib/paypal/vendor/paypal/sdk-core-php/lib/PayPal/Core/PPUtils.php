<?php
namespace PayPal\Core;
class PPUtils
{

	/**
	 *
	 * Convert a Name Value Pair (NVP) formatted string into
	 * an associative array taking care to urldecode array values
	 *
	 * @param string $nvpString
	 * @return array
	 */
	public static function nvpToMap($nvpString)
	{
		$ret = array();
		$params = explode("&", $nvpString);
		foreach ($params as $p) {
			list($k, $v) = explode("=", $p);
			$ret[$k] = urldecode($v);
		}
		return $ret;
	}



	/**
	 * Returns true if the array contains a key like $key
	 *
	 * @param array $map
	 * @param string $key
	 * @return bool
	 */
	public static function array_match_key($map, $key)
	{
		$replace = str_replace(array(
				'(',
				')',
				'.'
		), array(
				'\(',
				'\)',
				'\.'
		), $key);

		$pattern = "/$replace*/";

		foreach ($map as $k => $v) {
			preg_match($pattern, $k, $matches);
			if (count($matches) > 0) {
				return true;
			}
		}
		return false;
	}

    /**
     * Get the local IP address. The client address is a required
     * request parameter for some API calls
     */
    public static function getLocalIPAddress()
    {
        if (array_key_exists("SERVER_ADDR", $_SERVER) && self::isIPv4($_SERVER['SERVER_ADDR'])) {
            // SERVER_ADDR is available only if we are running the CGI SAPI
            return $_SERVER['SERVER_ADDR'];
        } else if(function_exists("gethostname") && self::isIPv4(gethostbyname(gethostname()))) {
            return gethostbyname(gethostname());
        } else {
            // fallback if nothing works
            return "127.0.0.1";
        }
    }

    /**
     * Determines if valid IPv4 or not
     *
     * @param $ip
     * @return bool
     */
    public static function isIPv4($ip) {
        return filter_var($ip, FILTER_VALIDATE_IP , FILTER_FLAG_IPV4);
    }

	/**
	 * Convert xml string to an intermediate nested array 
	 * representation that can be iterated
	 * 
	 * @param string $xmlInput	XML string to convert
	 */
	public static function xmlToArray($xmlInput)
	{
		$doc = new \DOMDocument();
		$doc->preserveWhiteSpace = false;
		$doc->loadXML($xmlInput);

		$results = $doc->getElementsByTagName("Body");
		if($results->length > 0 ){
			$node = $results->item(0);
			return PPUtils::xmlNodeToArray($node);
		} else {
			throw new \Exception("Unrecognized response payload ");
		}

	}

	/**
	 * Convert a DOM node to an intermediate nested array
	 * representation that can be iterated
	 * 
	 * @param DOMNode $node	DOM node to convert
	 */
	private static function xmlNodeToArray($node)
	{
		$result = array();

		$children = $node->childNodes;
		if(!empty($children))
		{	
			for($i = 0; $i < (int)$children->length; $i++)
			{
				$child = $children->item($i);
				if($child !== null)
				{
					if($child->childNodes->item(0) instanceof \DOMText )
					{
						$result[$i]['name'] = $child->nodeName;
						$result[$i]['text'] = $child->childNodes->item(0)->nodeValue;
						if($child->hasAttributes()) {
							foreach($child->attributes as $k => $v) {
								if($v->namespaceURI != 'http://www.w3.org/2001/XMLSchema-instance') {
									$result[$i]['attributes'][$v->name] = $v->value;
								}
							}
						}
					}
					else if(!in_array($child->nodeName, $result))
					{
						$result[$i]['name'] = $child->nodeName;
						$result[$i]['children'] = PPUtils::xmlNodeToArray($child);

						if($child->hasAttributes())
						{
							$attrs = $child->attributes;
							foreach($attrs as $k => $v)
							{
								if($v->namespaceURI != 'http://www.w3.org/2001/XMLSchema-instance')
								{

									$result[$i]['attributes'][$v->name] = $v->value;
								} 
							}
						}
					}
				}
			}
		}
		return $result;
	}

	/**
	 * Escapes invalid xml characters
	 *
	 * @param $textContent = xml data to be escaped
	 * @return string
	 */
	public static function escapeInvalidXmlCharsRegex($textContent)
	{
		return htmlspecialchars($textContent, (1 | 2), 'UTF-8', false);
	}



	/**
	 * Filter an array based on keys that match given prefix
	 *
	 * @param array $map
	 * @param string $keyPrefix
	 * @return array
	 */
	public static function filterKeyPrefix(array $map, $keyPrefix)
	{
		$filtered = array();
		foreach ($map as $key => $val) {
			if (($pos = stripos($key, $keyPrefix)) !== 0) {
				continue;
			}

			$filtered[substr_replace($key, '', 0, strlen($keyPrefix))] = $val;
		}

		return $filtered;
	}



	/**
	 * @var array|ReflectionProperty[]
	 */
	private static $propertiesRefl = array();

	/**
	 * @var array|string[]
	 */
	private static $propertiesType = array();



	/**
	 * Get property annotations for a certain property in a class
	 *
	 * @param string $class
	 * @param string $propertyName
	 * @throws RuntimeException
	 * @return string
	 */
	public static function propertyAnnotations($class, $propertyName)
	{
		$class = is_object($class) ? get_class($class) : $class;
		if (!class_exists('ReflectionProperty')) {
			throw new \RuntimeException("Property type of " . $class . "::{$propertyName} cannot be resolved");
		}

		if ($annotations =& self::$propertiesType[$class][$propertyName]) {
			return $annotations;
		}

		if (!($refl =& self::$propertiesRefl[$class][$propertyName])) {
			$refl = new \ReflectionProperty($class, $propertyName);
		}

		// todo: smarter regexp
		if (!preg_match_all('~\@([^\s@\(]+)[\t ]*(?:\(?([^\n@]+)\)?)?~i', $refl->getDocComment(), $annots, PREG_PATTERN_ORDER)) {
			return NULL;
		}
		foreach ($annots[1] as $i => $annot) {
			$annotations[strtolower($annot)] = empty($annots[2][$i]) ? TRUE : rtrim($annots[2][$i], " \t\n\r)");
		}

		return $annotations;
	}

	/**
	 * Determine if a property in a given class is a 
	 * attribute type.
	 *
	 * @param string $class
	 * @param string $propertyName
	 * @return string
	 */
	public static function isAttributeProperty($class, $propertyName) {
		if (($annotations = self::propertyAnnotations($class, $propertyName))) {
			return array_key_exists('attribute', $annotations);
		}
		return FALSE;
	}

	/**
	 * Determine if a property in a given class is a 
	 * collection type.
	 *
	 * @param string $class
	 * @param string $propertyName
	 * @return string
	 */
	public static function isPropertyArray($class, $propertyName) {
		if (($annotations = self::propertyAnnotations($class, $propertyName))) {
			if (isset($annotations['var']) && substr($annotations['var'], -2) === '[]') {
				return TRUE;

			} elseif (isset($annotations['array'])) {
				return TRUE;
			}
		}

		return FALSE;
	}



	/**
	 * Get data type of a property in a given class
	 *
	 * @param string $class
	 * @param string $propertyName
	 * @throws RuntimeException
	 * @return string
	 */
	public static function propertyType($class, $propertyName)
	{
		if (($annotations = self::propertyAnnotations($class, $propertyName)) && isset($annotations['var'])) {
			if (substr($annotations['var'], -2) === '[]') {
				return substr($annotations['var'], 0, -2);
			}

			return $annotations['var'];
		}

		return 'string';
	}

	/**
	 * 
	 * @param object $object
	 * @return array
	 */
	public static function objectProperties($object)
	{
		$props = array();
		foreach (get_object_vars($object) as $property => $default) {
			$annotations = self::propertyAnnotations($object, $property);
			if (isset($annotations['name'])) {
				$props[strtolower($annotations['name'])] = $property;
			}

			$props[strtolower($property)] = $property;
		}

		return $props;
	}



	/**
	 * Convert all array keys to lowercase
	 *
	 * @param array $array
	 * @return array
	 */
	public static function lowerKeys(array $array)
	{
		$ret = array();
		foreach ($array as $key => $value) {
			$ret[strtolower($key)] = $value;
		}

		return $ret;
	}

}
