@echo off
echo Installation de PHPMailer...
cd C:\xampp\htdocs\FootBookingApp
curl -sS https://getcomposer.org/installer | php
php composer.phar require phpmailer/phpmailer
echo Installation terminée !
pause
