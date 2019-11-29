<?php

const CAPTCHAKEY = "fd0be570abeb185938dce42da46b59f96b8954593f891f8029657205817b371b21ab75ca8085b39911cd35788329d52842928ac6498568914f64226e3a22df3e";
const CIPHERMETHOD = "AES256";

class Captcha
{
    private $phrase;

    public function __construct()
    {
        // Encrypt the plaintext passphrase and store it
        $phrasePlaintext = substr(md5(microtime()), 0, 5);
        $enc_iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(CIPHERMETHOD));
        $encrypted = openssl_encrypt($phrasePlaintext, CIPHERMETHOD, CAPTCHAKEY, 0, $enc_iv) . "::" . bin2hex($enc_iv);
        $this->phrase = $encrypted;
    }

    public function getPhrase()
    {
        // Decrypt and return the pass phrase
        list($encryptedString, $enc_iv) = explode("::", $this->phrase);
        return openssl_decrypt($encryptedString, CIPHERMETHOD, CAPTCHAKEY, 0, hex2bin($enc_iv));
    }
}