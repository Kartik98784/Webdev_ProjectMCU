<?php
session_start();

// Function to generate a random alphanumeric CAPTCHA
function generateCaptchaCode($length = 6) {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    $captcha_code = '';
    for ($i = 0; $i < $length; $i++) {
        $captcha_code .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $captcha_code;
}

// Generate CAPTCHA code and store it in the session
$captcha_code = generateCaptchaCode();
$_SESSION['captcha'] = $captcha_code;

// Create an image
$image = imagecreate(150, 50);
$bg_color = imagecolorallocate($image, 255, 255, 255); // White background
$text_color = imagecolorallocate($image, 0, 0, 0);     // Black text

// Path to Windows Arial font
$font_path = 'C:\Windows\Fonts\arial.ttf';

// Add the CAPTCHA text
if (file_exists($font_path)) {
    imagettftext($image, 20, 0, 15, 35, $text_color, $font_path, $captcha_code);
} else {
    die("Font not found at: $font_path");
}

// Output the image as PNG
header("Content-Type: image/png");
imagepng($image);
imagedestroy($image);
