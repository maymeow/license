<?php
declare(strict_types=1);

namespace MayMeow\License;

class RSACryptographyHelper
{
    public function generateRsaKeyPair(): array
    {
        $config = [
            "digest_alg" => "sha512",
            "private_key_bits" => 2048,           // Standard security level
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ];

        $res = openssl_pkey_new($config);

        openssl_pkey_export($res, $privateKey);
        $details = openssl_pkey_get_details($res);
        $publicKey = $details['key'];

        return [$privateKey, $publicKey];
    }

    public function encryptWithPrivate(string $plaintext, string $privateKey): string
    {
        openssl_private_encrypt($plaintext, $encrypted, $privateKey);
        return base64_encode($encrypted);
    }

    public function decryptWithPublic(string $encryptedData, string $publicKey): string
    {
        $dataToDecrypt = base64_decode($encryptedData);
        openssl_public_decrypt($dataToDecrypt, $decrypted, $publicKey);
        return $decrypted;
    }

    public function convertKeyToUnsignedArray(string $key): array
    {
        $keyLines = explode("\n", trim($key));
        $keyBody = '';
        foreach ($keyLines as $line) {
            if (strpos($line, '-----') === false) {
                $keyBody .= trim($line);
            }
        }
        $decodedKey = base64_decode($keyBody);
        return array_values(unpack('C*', $decodedKey));
    }

    public function convertUnsignedArrayToKey(array $unsignedArray, string $keyType = 'public'): string
    {
        $binaryData = '';
        foreach ($unsignedArray as $byte) {
            $binaryData .= chr($byte);
        }
        $base64Key = base64_encode($binaryData);

        $formattedKey = '';
        $lineLength = 64;
        for ($i = 0; $i < strlen($base64Key); $i += $lineLength) {
            $formattedKey .= substr($base64Key, $i, $lineLength) . "\n";
        }

        if ($keyType === 'public') {
            return "-----BEGIN PUBLIC KEY-----\n" . $formattedKey . "-----END PUBLIC KEY-----\n";
        } else {
            return "-----BEGIN PRIVATE KEY-----\n" . $formattedKey . "-----END PRIVATE KEY-----\n";
        }
    }
}