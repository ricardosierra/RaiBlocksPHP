<?php

class Uint
{
	public $u8;
	public $u4;
	public $u5;
	public $hex;
	public $string;
	
	public function __construct()
	{
		return $this;
	}
	
	protected function clean()
	{
		$this->u8 = $this->u4 = $this->u5 = $this->hex = $this->string = false;
	}
	
	public function fromHex($hex)
	{
		$this->clean();
		$this->hex = strtoupper($hex);
		$this->u8 = $this->hexU8($hex);
		return $this;
	}
	
	public static function fromHex($hex)
	{
		return (new Uint())->fromHex($hex);
	}
	
	public function fromUint8Array($u8)
	{
		$this->clean();
		$this->u8 = $u8;
		$this->hex = $this->u8Hex($u8);
		return $this;
	}
	
	public static function fromUint8Array($u8)
	{
		return (new Uint())->fromUint8Array($u8);
	}
	
	public function fromUint4Array($u4)
	{
		$this->clean();
		$this->u4 = $u4;
		$this->hex = $this->u4Hex($u4);
		$this->u8 = $this->u4U8($u4);
		return $this;
	}
	
	public static function fromUint4Array($u4)
	{
		return (new Uint())->fromUint4Array($u4);
	}
	
	public function fromString($str)
	{
		$this->clean();
		$this->u5 = $this->stringU5($str);
		$this->string = $str;
		$this->u4 = $this->u5U4($this->u5);
		$this->u8 = $this->u4U8($this->u4);
		return $this;
	}
	
	public static function fromString($str)
	{
		return (new Uint())->fromString($str);
	}
	
	public function fromDec($dec)
	{
		$this->clean();
		$this->dec = $dec;
		$this->hex = decToHex($dec);
		$this->u8 = $this->hexU8($this->hex);
		return $this;
	}
	
	public static function fromDec($dec)
	{
		return (new Uint())->fromDec($dec);
	}
	
	public function toHexString()
	{		
		if($this->hex)
			return $this->hex;
		else
			return $this->u8Hex($this->u8);
	}
	
	public function toString()
	{
		if($this->string)
			return $this->string;
		if($this->u5)
			return $this->u5String($this->u5);
		if($this->u4)
			return $this->u5String($this->u4U5($this->u4));
		if($this->u8)
			return $this->u5String($this->u4U5($this->u8U4($this->u8)));
		return false;
	}
	
	public function toUint8()
	{
		return $this->u8;
	}
	
	public function toUint4()
	{
		if($this->u4)
			return $this->u4;
		if($this->u8)
			return $this->u8U4($this->u8);
		return false;
	}
	
	public function hexU8($hex)
	{
		$arr = new SplFixedArray(strlen($hex) / 2);
		for($i = 0; $i < strlen($hex); $i+=2)
		{
			$arr[$i/2] = base_convert($hex[$i] . $hex[$i+1], 16, 10);
		}
		return $arr;
	}
	
	public function hexU4($hex)
	{
		$arr = new SplFixedArray(strlen($hex));
		for($i = 0; $i < strlen($hex); $i++)
		{
			$arr[$i] = base_convert($hex[$i], 16, 10);
		}
		return $arr;
	}
	
	public function u8Hex($bytes)
	{
		$hex = '';
		foreach($bytes as $byte)
		{
			$aux = base_convert($byte, 10, 16);
			if(strlen($aux) == 1)
				$aux = '0' . $aux;
			$hex .= $aux;
		}
		return strtoupper($hex);
	}
	
	public function u4Hex($bytes)
	{
		$hex = '';
		foreach($bytes as $byte)
			$hex .= base_convert($byte, 10, 16);
		return strtoupper($hex);
	}
	
	public function u8U4($u8)
	{
		$u4 = new SplFixedArray(count($u8) * 2);
		for($i = 0; $i < count($u8); $i++)
		{
			$u4[$i*2] = $u8[$i] / 16 | 0;
			$u4[$i*2 + 1] = $u8[$i] % 16;
		}
		return $u4;
	}
	
	public function u4U8($u4)
	{
		$u8 = new SplFixedArray(count($u4) / 2);
		for($i = 0; $i < count($u8); $i++)
			$u8[$i] = $u4[$i*2] * 16 + $u4[$i*2 + 1];
		return $u8;
	}
	
	public function u4U5($u4)
	{
		$u5 = new SplFixedArray(count($u4) / 5 * 4);
		for($i = 1; $i <= count($u5); $i++)
		{
			$n = $i - 1;
			$m = $i % 4;
			$z = $n + (($i - $m) / 4);
			$right = $u4[$z] << $m;
			if((count($u5) - $i) % 4 == 0)
				$left = $u4[$z - 1] << 4;
			else
				$left = $u4[$z + 1] >> (4 - $m);
			$u5[$n] = ($left + $right) % 32;
		}
		return $u5;
	}
	
	public function u5U4($u5)
	{
		$u4 = new SplFixedArray(count($u5) / 4 * 5);
		for($i = 1; $i <= count($u4); $i++)
		{
			$n = $i - 1;
			$m = $i % 5;
			$z = $n - (($i - $m) / 5);
			if($i > 1)
				$right = $u5[$z - 1] << (5 - $m);
			else $right = 0;
			$left = $u5[$z] >> $m;
			$u4[$n] = ($left + $right) % 16;
		}
		return $u4;
	}
	
	public function stringU5($str)
	{
		$letters = '13456789abcdefghijkmnopqrstuwxyz';
		$len = strlen($str);
		$arr = str_split($str);
		$u5 = new SplFixedArray($len);
		for($i = 0; $i < $len; $i++)
			$u5[$i] = strpos($letters, $arr[$i]);
		return $u5;
	}
	
	public function u5String($u5)
	{
		$letters = str_split('13456789abcdefghijkmnopqrstuwxyz');
		$str = "";
		for($i = 0; $i < count($u5); $i++)
			$str .= $letters[$u5[$i]];
		return $str;
	}
	
	public function dec2hex($str, $bytes = null)
	{
		$dec = str_split($hex);
		$sum = [];
		$hex = [];
		$i = $s = 0;
		while(count($dec))
		{
			$s = 1 * array_shift($dec);
			for($i = 0; $s || $i < count($sum); $i++)
			{
				$s += ($sum[$i] || 0) * 10;
				$sum[$i] = $s % 16;
				$s = ($s - $sum[$i]) / 16;
			}
		}
		while(count($sum))
		{
			$hex[] = base_convert(array_shift($sum), 10, 16);
		}
		
		$hex = implode('', $hex);

		if(strlen($hex) % 2 != 0)
			$hex = "0" . $hex;

		if($bytes > strlen($hex) / 2)
		{
			$diff = $bytes - strlen($hex) / 2;
			for($i = 0; $i < $diff; $i++)
				$hex = "00" . $hex;
		}
		return $hex;
	}
}


// from here > http://www.danvk.org/hex2dec.html

/**
 * A function for converting hex <-> dec w/o loss of precision.
 *
 * The problem is that parseInt("0x12345...") isn't precise enough to convert
 * 64-bit integers correctly.
 *
 * Internally, this uses arrays to encode decimal digits starting with the least
 * significant:
 * 8 = [8]
 * 16 = [6, 1]
 * 1024 = [4, 2, 0, 1]
 */

// Adds two arrays for the given base (10 or 16), returning the result.
// This turns out to be the only "primitive" operation we need.
function add($x, $y, $base)
{
	$z = [];
	$n = max(count($x), count($y));
	$carry = 0;
	$i = 0;
	while ($i < $n || $carry) 
	{
		$xi = $i < count($x) ? $x[$i] : 0;
		$yi = $i < count($y) ? $y[$i] : 0;
		$zi = $carry + $xi + $yi;
		$z[] = $zi % $base;
		$carry = floor($zi / $base);
		$i++;
	}
	return $z;
}

function multiplyByNumber($num, $x, $base) {
	if ($num < 0) return null;
	if ($num == 0) return [];
	
	$result = [];
	$power = $x;
	while (true)
	{
		if ($num & 1)
		{
			$result = add($result, $power, $base);
		}
		$num = $num >> 1;
		if ($num === 0)
			break;
		$power = add($power, $power, $base);
	}
	
	return $result;
}

function parseToDigitsArray($str, $base) {
	$digits = str_split($str);
	$ary = [];
	for ($i = count($digits) - 1; $i >= 0; $i--) 
	{
		$n = intval($digits[$i], $base);
		if (!is_numeric($n)) return null;
		$ary[] = $n;
	}
	return $ary;
}

function convertBase($str, $fromBase, $toBase) {
  $digits = parseToDigitsArray($str, $fromBase);
  if ($digits === null) return null;

  $outArray = [];
  $power = [1];
  for ($i = 0; $i < count($digits); $i++) {
    // invariant: at this point, fromBase^i = power
    if ($digits[$i]) 
    {
      $outArray = add($outArray, multiplyByNumber($digits[$i], $power, $toBase), $toBase);
    }
    $power = multiplyByNumber($fromBase, $power, $toBase);
  }

  $out = '';
  for ($i = count($outArray) - 1; $i >= 0; $i--) {
    $out .= base_convert($outArray[$i], $fromBase, $toBase);
  }
  return $out;
}

function decToHex($decStr) {
  $hex = convertBase($decStr, 10, 16);
  if(strlen($hex) % 2 != 0)
	$hex = '0' . $hex;
  return $hex ? $hex : null;
}

function hexToDec($hexStr) {
  if (substr($hexStr, 0, 2) === '0x') $hexStr = substr($hexStr, 2);
  $hexStr = strtolower($hexStr);
  return convertBase($hexStr, 16, 10);
}
