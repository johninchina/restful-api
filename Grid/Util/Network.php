<?php
namespace Grid\Util;

class Network
{
	// 判断ip是否合法
	public static function isIp($network)
	{
		if (self::isIpv4($network))
			return true;
		if (self::isIpv6($network))
			return true;
	}
	
	// 判断网段是否合法
	public static function isSegment($network, $netmask, &$error = false)
	{
		$isSegment = true;
		if (self::isIpv4($network)) {
			if ($netmask > 32 || $netmask < 1){
				$error = 'NETMASK_INVALID';
				return false;
			}
			$isSegment = self::isIpv4Segment($network, $netmask);
		}
		if (self::isIpv6($network)) {
			if($netmask > 128 || $netmask < 1){
				$error = 'NETMASK_INVALID';
				return false;
			}
			$isSegment = self::isIpv6Segment($network, $netmask);
		}
		if (!$isSegment) {
			$error = 'SEGMENT_INVALID';
		}
		return $isSegment;
	}

	// 获取网段的二进制
	// 获取ip所在的网段的二进制
	public static function getSegment($network, $netmask = false)
	{
		if (self::isIpv4($network))
			return self::getIpv4Segment($network, $netmask);
		if (self::isIpv6($network))
			return self::getIpv6Segment($network, $netmask);
		return null;
	}
	
	// 判断是否是ipv4
	public static function isIpv4($network)
	{
		return filter_var($network, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
	}
	
	// 判断是否是ipv6
	public static function isIpv6($network)
	{
		return filter_var($network, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
	}
	
	// 判断ipv4网段是否合法
	public static function isIpv4Segment($network, $netmask)
	{
		$networkLong = self::ipv4ToLong($network);
		$segment = self::getIpv4Segment($network, $netmask);
		return $networkLong === $segment ? true : false;
	}
	
	// 判断ipv6网段是否合法
	public static function isIpv6Segment($network, $netmark)
	{
		$networkLong = self::ipv6ToLong($network);
		$segment = self::getIpv6Segment($network, $netmark);
		return $network === $segment ? true : false;
	}
	
	// 获取ipv4网段的二进制
	// 获取ipv4类型的ip所在的网段的二进制
	public static function getIpv4Segment($network, $netmask = false)
	{
		$netmask = $netmask !== false ? $netmask : 24;
		$networkLong = self::ipv4ToLong($network);
		$netmaskLong = self::ipv4MaskToLong($netmask);
		return $networkLong & $netmaskLong;
	}
	
	// 获取ipv6网段的二进制
	// 获取ipv6类型的ip所在的网段的二进制
	public static function getIpv6Segment($network, $netmask = false)
	{
		$netmask = $netmask !== false ? $netmask : 64;
		$networkLong = self::ipv6ToLong($network);
		$netmaskLong = self::ipv6MaskToLong($netmask);
		return $networkLong & $netmaskLong;
	}
	
	// 转换ipv4类型的ip为二进制
	public static function ipv4ToLong($network)
	{
		return str_pad(base_convert(ip2long($network), 10, 2), 32, 0, STR_PAD_LEFT);
	}
	
	// 转换ipv6类型的ip为二进制
	public static function ipv6ToLong($network)
	{
		$networkPack = inet_pton($network);
		$networkLong = '';
		$bits = 15;
		while ($bits >= 0) {
			$bin = sprintf("%08b", (ord($networkPack[$bits])));
			$networkLong = $bin.$networkLong;
			$bits--;
		}
		return $networkLong;
	}
	
	// 转换ipv4类型的掩码为二进制
	public static function ipv4MaskToLong($netmask)
	{
		$binStr = str_repeat(1, $netmask);
		$binStr = str_pad($binStr, 32, 0);
		return $binStr;
	}
	
	// 转换ipv6类型的掩码为二进制
	public static function ipv6MaskToLong($netmask)
	{
		$binStr = str_repeat(1, $netmask);
		$binStr = str_pad($binStr, 128, 0);
		return $binStr;
	}
	
	// 获取当前服务器ip
	public static function getClientIp()
	{
		if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
			$ip = getenv("HTTP_CLIENT_IP");
		else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
			$ip = getenv("HTTP_X_FORWARDED_FOR");
		else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
			$ip = getenv("REMOTE_ADDR");
		else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
			$ip = $_SERVER['REMOTE_ADDR'];
		else
			$ip = "unknown";
		return($ip);
	}
}