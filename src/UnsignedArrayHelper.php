<?php
declare(strict_types=1);

namespace MayMeow\License;

class UnsignedArrayHelper
{
    public static function convertKeyToUnsignedArray(string $key): array
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

    public static function convertUnsignedArrayToKey(array $unsignedArray, string $keyType = 'public'): string
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