<?php
namespace Svgta\EasyApi\utils;
use Jose\Component\KeyManagement\JWKFactory;
use Svgta\EasyApi\utils\conf;
use Svgta\EasyApi\utils\JWE;
use Jose\Component\Core\JWK as joseJWK;
use Svgta\EasyApi\utils\utils;
use Jose\Component\Core\Util\RSAKey;

class JWK{
  public static function getOct(string $enc){
    $dec = json_decode(JWE::decrypt($enc), TRUE);
    return JWKFactory::createFromValues($dec);
  }

  public static function createOctFromArray(array $ar){
    return JWKFactory::createFromValues($ar);
  }
  public static function createOctJWK(string $pwd, string $alg = 'A256KW', string $type = 'enc'){
    $key = JWKFactory::createFromSecret(
      $pwd,
      [
        'alg' => $alg,
        'use' =>'enc',
      ]
    );

    return $key;
  }

  public static function genEncOct(string $pwd, string $alg = 'A256KW'){
    $key = self::createOctJWK($pwd, $alg, 'enc');
    return JWE::encrypt($key);
  }

  public static function genEncRsa(int $size = 2048, string $alg = 'RSA-OAEP-256'): array {
      return self::genRsa($size, $alg, 'enc');
  }
  public static function genSigRsa(int $size = 2048, string $alg = 'RS256'): array {
      return self::genRsa($size, $alg, 'sig');
  }
  public static function genRsa(int $size = 2048, string $alg = 'RS256', string $type = 'sig'): array {
    $jwk = JWKFactory::createRSAKey(
        $size, // Size in bits of the key. We recommend at least 2048 bits.
        [
            'alg' => $alg,
            'use' => $type,    // This key is used for signature/verify operations only
            'kid' => utils::genUUID(),
          ]);
    $kid = utils::genUUID();
    $kClass = \Jose\Component\Core\Util\RSAKey::createFromJWK($jwk);
    $key = [
      'privateKey_encrypted' => JWE::encrypt($jwk),
      'publicKey' => json_decode(json_encode($jwk->toPublic()), TRUE),
      'exp' => time() + conf::getConfKey('CONF_GENERAL', 'jwk_lifetime'),
      'exp_verify' => time() + conf::getConfKey('CONF_GENERAL', 'jwk_lifetime') + conf::getConfKey('CONF_GENERAL', 'token_lifetime'),
      'kid' => $jwk->get('kid'),
    ];

    return $key;
  }

  public static function toPEM(joseJWK $jwk){
    return RSAKey::createFromJWK($jwk)->toPEM();
  }

  public static function getKey(string $enc): array {
    $payload = JWE::decrypt($enc);
    return json_decode($payload, TRUE);
  }

  public static function createFormValue(array $value = []): joseJWK{
    return JWKFactory::createFromValues($value);
  }
}
