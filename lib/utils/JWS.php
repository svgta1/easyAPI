<?php
namespace Svgta\EasyApi\utils;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Core\JWK;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\Serializer\JWSSerializerManager;
use Jose\Component\Signature\Serializer\CompactSerializer;
use Jose\Component\Signature\JWSVerifier;
use Jose\Component\Signature\JWSLoader;
use Jose\Component\Core\JWKSet;
use Svgta\EasyApi\utils\conf;

class JWS{
  public static function signPayload(array $payload, array $privateKey): string{
    $alg = $privateKey['alg'];
    $class = 'Jose\\Component\\Signature\\Algorithm\\' . $alg;
    $algorithmManager = new AlgorithmManager([
      new $class(),
    ]);
    $jwk = new JWK($privateKey);
    $jwsBuilder = new JWSBuilder($algorithmManager);

    $payload['exp'] = time() + conf::getConfKey('CONF_GENERAL', 'token_lifetime');
    $payload['iat'] = time();
    $payload['nbf'] = time();
    $payload['iss'] = conf::getConfKey('CONF_GENERAL', 'appName');

    $jws = $jwsBuilder
      ->create()                               // We want to create a new JWS
      ->withPayload(json_encode($payload))                  // We set the payload
      ->addSignature($jwk, [
        'alg' => $alg,
        'typ' => 'JWT',
        'kid' => $privateKey['kid'],
      ]) // We add a signature with a simple protected header
      ->build();

    $serializer = new CompactSerializer();
    $token = $serializer->serialize($jws, 0);

    return $token;
  }

  public static function verifySignKeySet(string $token, $keySet){
    $v = self::verify($token);
    if($v['jwsVerifier']->verifyWithKeySet($v['jws'], JWKSET::createFromKeyData($keySet), 0))
      return json_decode($v['jws']->getPayload(), TRUE);

    return null;

  }

  public static function verifySign(string $token, array $publicKey){
    $v = self::verify($token);
    $jwk = new JWK($publicKey);
    if($v['jwsVerifier']->verifyWithKey($v['jws'], $jwk, 0))
      return json_decode($v['jws']->getPayload(), TRUE);

    return null;
  }

  private static function verify(string $token): array{
    list($header, $payload, $sign) = explode('.', $token);
    $h = json_decode(base64_decode($header), TRUE);
    $alg = $h['alg'];
    $class = 'Jose\\Component\\Signature\\Algorithm\\' . $alg;
    $algorithmManager = new AlgorithmManager([
      new $class(),
    ]);
    $jwsVerifier = new JWSVerifier(
      $algorithmManager
    );
    $serializerManager = new JWSSerializerManager([
      new CompactSerializer(),
    ]);
    $jws = $serializerManager->unserialize($token);

    return [
      'jws' => $jws,
      'jwsVerifier' => $jwsVerifier,
    ];
  }
}
