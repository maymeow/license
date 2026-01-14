<?php
declare(strict_types=1);

namespace MayMeow\License;

class ECCryptographyHelper
{
    function generateEcKeyPair(): array
    {
        $config = [
            'private_key_type' => OPENSSL_KEYTYPE_EC,
            'curve_name' => 'prime256v1',
        ];

        $res = openssl_pkey_new($config);

        openssl_pkey_export($res, $privateKey);
        $details = openssl_pkey_get_details($res);
        $publicKey = $details['key'];

        return [$privateKey, $publicKey];
    }

    function hkdf(string $ikm, int $length = 32, string $info = 'ecies'): string
    {
        return hash_hkdf('sha256', $ikm, $length, $info);
    }

    function deriveSharedSecret(string $privateKeyPem, string $publicKeyPem): string
    {
        $privateKey = openssl_pkey_get_private($privateKeyPem);
        $publicKey  = openssl_pkey_get_public($publicKeyPem);

        return openssl_pkey_derive($publicKey, $privateKey, 32);
    }

    function encryptString(string $plaintext, string $sharedSecret): array
    {
        $key = $this->hkdf($sharedSecret, 32);
        $iv  = random_bytes(12); // GCM standard IV size

        $ciphertext = openssl_encrypt(
            $plaintext,
            'aes-256-gcm',
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        return [
            'ciphertext' => base64_encode($ciphertext),
            'iv'         => base64_encode($iv),
            'tag'        => base64_encode($tag),
        ];
    }

    function decryptString(array $payload, string $sharedSecret): string
    {
        $key = $this->hkdf($sharedSecret, 32);

        return openssl_decrypt(
            base64_decode($payload['ciphertext']),
            'aes-256-gcm',
            $key,
            OPENSSL_RAW_DATA,
            base64_decode($payload['iv']),
            base64_decode($payload['tag'])
        );
    }

    public function sign(string $data, string $privateKeyPem): string
    {
        openssl_sign($data, $signature, $privateKeyPem, OPENSSL_ALGO_SHA256);
        return base64_encode($signature);
    }

    public function verify(string $data, string $signature, string $publicKeyPem): bool
    {
        $decodedSignature = base64_decode($signature);
        return openssl_verify($data, $decodedSignature, $publicKeyPem, OPENSSL_ALGO_SHA256) === 1;
    }
}