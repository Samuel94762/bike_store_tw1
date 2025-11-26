#!/bin/bash

# Script para instalar PHPMailer usando Composer
cd /xampp/htdocs/bike_store_tw1

# Descargar PHPMailer desde GitHub
mkdir -p libs/phpmailer

# Descargar manualmente si no tienes composer
wget -O /tmp/phpmailer.zip https://github.com/PHPMailer/PHPMailer/releases/download/v6.8.0/PHPMailer-6.8.0.zip
unzip -o /tmp/phpmailer.zip -d libs/
mv libs/PHPMailer-6.8.0 libs/phpmailer

echo "PHPMailer instalado correctamente"
