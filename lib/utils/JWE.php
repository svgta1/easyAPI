<?php
namespace Svgta\EasyApi\utils;
use Svgta\EasyApi\utils\conf;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Encryption\Compression\CompressionMethodManager;
use Jose\Component\Encryption\Compression\Deflate;
use Jose\Component\Encryption\JWEBuilder;
use Jose\Component\Encryption\JWEDecrypter;
use Jose\Component\Core\JWK;
use Jose\Component\Encryption\Serializer\JWESerializerManager;
use Jose\Component\Encryption\Serializer\CompactSerializer;
use Jose\Component\KeyManagement\JWKFactory;

class JWE{
  const DEFAULT_ENCALG = 'A256KW';
  const DEFAULT_ENCENC = 'A256CBC-HS512';
  public static function encrypt($payload, ?JWK $jwk = null, ?string $encAlg = null): string {
    if(!$encAlg)
      $encAlg = self::DEFAULT_ENCENC;

    if($jwk)
      $alg = $jwk->get('alg');
    else
      $alg = self::DEFAULT_ENCALG;

    $keyEncClass =  '\\Jose\\Component\\Encryption\\Algorithm\\KeyEncryption\\' . str_replace('-', '', $alg);
    $keyEncryptionAlgorithmManager = new AlgorithmManager([
      new $keyEncClass(),
    ]);
    $encClass = 'Jose\\Component\\Encryption\\Algorithm\\ContentEncryption\\' . str_replace('-', '', $encAlg);
    $contentEncryptionAlgorithmManager = new AlgorithmManager([
      new $encClass(),
    ]);
    $compressionMethodManager = new CompressionMethodManager([
      new Deflate(),
    ]);
    $jweBuilder = new JWEBuilder(
        $keyEncryptionAlgorithmManager,
        $contentEncryptionAlgorithmManager,
        $compressionMethodManager
    );

    if(!is_string($payload))
      $payload = json_encode($payload);

    if($jwk === null)
      $jwk = self::getKey();
    $jwe = $jweBuilder
      ->create()              // We want to create a new JWE
      ->withPayload($payload) // We set the payload
      ->withSharedProtectedHeader([
        'alg' => $alg,        // Key Encryption Algorithm
        'enc' => $encAlg, // Content Encryption Algorithm
        //'zip' => 'DEF'            // We enable the compression (irrelevant as the payload is small, just for the example).
      ])
      ->addRecipient($jwk)    // We add a recipient (a shared key or public key).
      ->build();              // We build it

    $serializer = new CompactSerializer();
    return $serializer->serialize($jwe, 0);
  }

  public static function decrypt(string $token, ?JWK $jwk = null){
    list($header) = explode('.', $token);
    if(!$header = json_decode(base64_decode($header)))
      return null;
    if(!isset($header->alg) AND !isset($header->enc))
      return null;

    $alg = $header->alg;
    $enc = $header->enc;

    $keyEncClass =  '\\Jose\\Component\\Encryption\\Algorithm\\KeyEncryption\\' . str_replace('-', '', $alg);
    $keyEncryptionAlgorithmManager = new AlgorithmManager([
        new $keyEncClass(),
    ]);

    $encClass = 'Jose\\Component\\Encryption\\Algorithm\\ContentEncryption\\' . str_replace('-', '', $enc);
    $contentEncryptionAlgorithmManager = new AlgorithmManager([
        new $encClass(),
    ]);
    $compressionMethodManager = new CompressionMethodManager([
        new Deflate(),
    ]);
    $jweDecrypter = new JWEDecrypter(
        $keyEncryptionAlgorithmManager,
        $contentEncryptionAlgorithmManager,
        $compressionMethodManager
    );

    if($jwk === null)
      $jwk = self::getKey();
    $serializerManager = new JWESerializerManager([
        new CompactSerializer(),
    ]);
    $jwe = $serializerManager->unserialize($token);
    if($jweDecrypter->decryptUsingKey($jwe, $jwk, 0))
      return $jwe->getPayload();
    return null;
  }

  private static function getKey(): JWK{
    $secretKey = conf::getConfKey('CONF_SECURITY_KEY', 'securityKey');
    return JWKFactory::createFromValues($secretKey);
  }

}
