<?php

namespace App;

class CryptoKeyPair
{
    private array $keys;
    private string $nonce;

    public function __construct()
    {
        $this->keys = [];
        $this->nonce = random_bytes(SODIUM_CRYPTO_BOX_NONCEBYTES);
    }

    public function getNonce(): string
    {
        return $this->nonce;
    }

    public function getKeys(int $id): array
    {
        return $this->keys[$id];
    }

    public function registerKeys(int $id, ?string $publicKey = null, ?string $privateKey = null)
    {
        $this->keys[$id] = [
            $publicKey,
            $privateKey
        ];
    }

    public function generateKeys(int $id): array
    {
        $keypair = sodium_crypto_box_keypair();
        return $this->keys[$id] = [
            sodium_crypto_box_publickey($keypair),
            sodium_crypto_box_secretkey($keypair)
        ];
    }

    public function encrypt(string $message, int $to, int $from): string
    {
        $encryptionKey = sodium_crypto_box_keypair_from_secretkey_and_publickey(
            $this->keys[$to][1],
            $this->keys[$from][0]
        );
        $encrypted = sodium_crypto_box($message, $this->nonce, $encryptionKey);
        return base64_encode($encrypted);
    }

    public function decrypt(string $encryptedMessage, int $from, int $to): string
    {
        $decryptedKey = sodium_crypto_box_keypair_from_secretkey_and_publickey(
            $this->keys[$from][1],
            $this->keys[$to][0]
        );
        $message = sodium_crypto_box_open($encryptedMessage, $this->nonce, $decryptedKey);

        if ($message === false) {
            throw new \SodiumException('Fail decrypt message');
        }

        return $message;
    }
}
