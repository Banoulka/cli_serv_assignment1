<?php

const CAPTCHAKEY = "fd0be570abeb185938dce42da46b59f96b8954593f891f8029657205817b371b21ab75ca8085b39911cd35788329d52842928ac6498568914f64226e3a22df3e";
const CIPHERMETHOD = "AES256";

class Captcha
{
    private $phrase;

    public function __construct()
    {
        $this->resetPhrase();
    }

    public function resetPhrase()
    {
        // Encrypt the plaintext passphrase and store it
        $phrasePlaintext = substr(md5(microtime()), 0, 5);
        $enc_iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(CIPHERMETHOD));
        $encrypted = openssl_encrypt($phrasePlaintext, CIPHERMETHOD, CAPTCHAKEY, 0, $enc_iv) . "::" . bin2hex($enc_iv);
        $this->phrase = $encrypted;
        Session::setSession("captcha", serialize($this));
    }

    public function getPhrase()
    {
        // Decrypt and return the pass phrase
        list($encryptedString, $enc_iv) = explode("::", $this->phrase);
        return openssl_decrypt($encryptedString, CIPHERMETHOD, CAPTCHAKEY, 0, hex2bin($enc_iv));
    }

    public function getImage()
    {
        $imageHeight = 60;
        $imageWidth = 250;
        $image = imagecreatetruecolor($imageWidth, $imageHeight);

        $captchaLines = rand(10, 30);
        $captchaDots = rand(60, 100);
        $captchaFontSize = $imageHeight * 0.65;
        $captchaFont = dirname(__FILE__) . "/monofont.ttf";

        imageantialias($image, true);

        $backgroundColour = imagecolorallocate($image, rand(125, 175), rand(125, 175), rand(125, 175));

        // Text colours
        $textColour = imagecolorallocate($image, rand(0, 125), rand(0, 124), rand(0, 150));
        $textColourTwo = imagecolorallocate($image, rand(0, 125), rand(0, 124), rand(0, 150));
        $textColourThree = imagecolorallocate($image, rand(0, 125), rand(0, 124), rand(0, 150));
        $black = imagecolorallocate($image, 0, 0, 0);
        $white = imagecolorallocate($image, 255, 255, 255);
        $colours = [$textColour, $textColourTwo, $textColourThree, $black, $white];

        // Noise colours
        $noiseColour = imagecolorallocate($image, rand(0, 255), rand(0, 255), rand(0, 244));
        $noiseColourBlack = imagecolorallocate($image, 0, 0, 0);
        $noiseColourWhite = imagecolorallocate($image, 255, 255, 255);
        $noiseColours = [$noiseColour, $noiseColourBlack, $noiseColourWhite];

        // Fill the background
        imagefill($image, 0, 0, $backgroundColour);

        // Add the circle noise
        for($count=0; $count < $captchaDots; $count++ ) {
            imagefilledellipse($image, mt_rand(0, $imageWidth), mt_rand(0, $imageHeight), rand(1, 5), rand(2, 6), $noiseColours[rand(0, 2)]);
        }

        // Add the lines
        for($count=0; $count < $captchaLines; $count++ ) {
            imageline(
                $image,
                mt_rand(0, $imageWidth),
                mt_rand(0, $imageHeight),
                mt_rand(0, $imageWidth),
                mt_rand(0, $imageHeight), $noiseColours[rand(0, 2)] );
        }

        // Write the text
        $phrase = $this->getPhrase();
        $text_box = imagettfbbox($captchaFontSize, 0, $captchaFont , $phrase);

        $inital = ($imageWidth - $text_box[4])/3;
        $y = ($imageHeight - $text_box[5])/2;

        for ($i = 0; $i < strlen($phrase); $i++) {
            $letter_space = 170/strlen($phrase);
            imagettftext($image, $captchaFontSize, rand(-15, 15), $inital + $i*$letter_space, rand($y-5, $y+5), $colours[rand(0, count($colours)-1)], $captchaFont, $phrase[$i]);
        }


        return $image;
    }
}