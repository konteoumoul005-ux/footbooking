<?php
// Fonction d'envoi d'email avec PHPMailer
// Placez ce fichier à la racine : C:\xampp\htdocs\FootBookingApp\send_email.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function envoyerEmailConfirmation($destinataire, $nom, $reservation) {
    require_once 'vendor/autoload.php';

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'footbooking0@gmail.com';
        $mail->Password   = 'ayfk ybnc bpvo jjes';
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom('footbooking0@gmail.com', 'FootBooking');
        $mail->addAddress($destinataire, $nom);

        $mail->isHTML(true);
        $mail->Subject = '✅ Votre réservation FootBooking est confirmée !';
        $mail->Body    = '
        <!DOCTYPE html>
        <html>
        <head><meta charset="UTF-8"></head>
        <body style="font-family: Open Sans, sans-serif; background:#f4f6f9; margin:0; padding:0;">
            <div style="max-width:600px; margin:30px auto; background:#fff; border-radius:16px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,0.1);">
                
                <!-- Header -->
                <div style="background:linear-gradient(135deg,#1abc9c,#16a085); padding:30px; text-align:center;">
                    <h1 style="color:#fff; margin:0; font-size:28px;">⚽ FootBooking</h1>
                    <p style="color:rgba(255,255,255,0.9); margin:5px 0 0;">Réservation confirmée</p>
                </div>

                <!-- Body -->
                <div style="padding:30px;">
                    <h2 style="color:#2d3a4a;">Bonjour ' . htmlspecialchars($nom) . ' 👋</h2>
                    <p style="color:#555;">Votre réservation a été <strong style="color:#1abc9c;">confirmée</strong> par l\'administrateur. Voici les détails :</p>

                    <div style="background:#f8f9fa; border-radius:10px; padding:20px; margin:20px 0;">
                        <table style="width:100%; border-collapse:collapse;">
                            <tr>
                                <td style="padding:8px 0; color:#888; font-size:14px;">🏟️ Terrain</td>
                                <td style="padding:8px 0; font-weight:700; color:#2d3a4a;">' . htmlspecialchars($reservation['terrain']) . '</td>
                            </tr>
                            <tr>
                                <td style="padding:8px 0; color:#888; font-size:14px;">📅 Date</td>
                                <td style="padding:8px 0; font-weight:700; color:#2d3a4a;">' . date('d/m/Y', strtotime($reservation['date_reservation'])) . '</td>
                            </tr>
                            <tr>
                                <td style="padding:8px 0; color:#888; font-size:14px;">⏰ Horaires</td>
                                <td style="padding:8px 0; font-weight:700; color:#2d3a4a;">' . $reservation['heure_debut'] . ' - ' . $reservation['heure_fin'] . '</td>
                            </tr>
                            <tr>
                                <td style="padding:8px 0; color:#888; font-size:14px;">⚽ Type</td>
                                <td style="padding:8px 0; font-weight:700; color:#2d3a4a;">' . ucfirst($reservation['type_terrain']) . '</td>
                            </tr>
                            <tr>
                                <td style="padding:8px 0; color:#888; font-size:14px;">💰 Montant total</td>
                                <td style="padding:8px 0; font-weight:700; color:#1abc9c;">' . number_format($reservation['montant'], 0, ',', ' ') . ' FCFA</td>
                            </tr>
                        </table>
                    </div>

                    <div style="background:#e8f8f5; border-left:4px solid #1abc9c; padding:15px; border-radius:0 8px 8px 0; margin-bottom:20px;">
                        <p style="margin:0; color:#0e7a5a; font-size:14px;">
                            <strong>À noter :</strong> Le reste du montant sera payé sur place le jour du match.
                        </p>
                    </div>

                    <p style="color:#555;">Bonne chance et bon match ! ⚽🎉</p>
                </div>

                <!-- Footer -->
                <div style="background:#2d3a4a; padding:20px; text-align:center;">
                    <p style="color:#8a9bae; font-size:12px; margin:0;">© 2026 FootBooking · Tous droits réservés</p>
                </div>
            </div>
        </body>
        </html>';

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email error: " . $mail->ErrorInfo);
        return false;
    }
}