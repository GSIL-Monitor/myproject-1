<?php

class AESCryptHandler {
	/**
	 * 算法,另外还有192和256两种长度
	 */
	const CIPHER = MCRYPT_RIJNDAEL_128;
	/**
	 * 模式
	 */
	// const MODE = MCRYPT_MODE_ECB;
	const MODE = MCRYPT_MODE_CBC;

	/**
	 * 加密
	 *
	 * @param string $key
	 *            密钥
	 * @param string $str
	 *            需加密的字符串
	 * @return type
	 */
	static public function encrypt($str, $key, $iv) {
		if (!isset($iv)) {
			$iv = mcrypt_create_iv(mcrypt_get_iv_size(self::CIPHER, self::MODE), MCRYPT_RAND);
		}
		// $result = mcrypt_encrypt(self::CIPHER, $key, self::pad($str), self::MODE);
		$result = mcrypt_encrypt(self::CIPHER, $key, $str, self::MODE, $iv);
		$result = base64_encode($result);

		return $result;
	}

	/**
	 * 解密
	 *
	 * @param type $key
	 * @param type $str
	 * @return type
	 */
	static public function decrypt($str, $key, $iv) {
		$str = base64_decode($str);
		if (!isset($iv)) {
			$iv = mcrypt_create_iv(mcrypt_get_iv_size(self::CIPHER, self::MODE), MCRYPT_RAND);
		}
		// return mcrypt_decrypt(self::CIPHER, $key, self::pad($str), self::MOD);
		return mcrypt_decrypt(self::CIPHER, $key, $str, self::MODE, $iv);
	}

	static private function pad($data, $blocksize = 16) {
		$pad = $blocksize - (strlen($data) % $blocksize);
		return $data . str_repeat(chr($pad), $pad);
	}
}

?>