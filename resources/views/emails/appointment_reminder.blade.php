<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rappel de Consultation Médicale</title>
    <style>
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
        table { border-collapse: collapse !important; }
        body { height: 100% !important; margin: 0 !important; padding: 0 !important; width: 100% !important; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; }
    </style>
</head>
<body style="margin: 0; padding: 24px 12px; background-color: #f1f5f9; color: #1e293b;">

    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
        
        <!-- En-tête Institutionnel -->
        <tr>
            <td style="padding: 32px 32px 24px 32px; background-color: #0f172a; text-align: left;">
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                        <td>
                            <div style="font-size: 20px; font-weight: 800; letter-spacing: -0.5px; color: #ffffff; text-transform: uppercase;">
                                HÔPITAL <span style="color: #38bdf8;">RDV</span>
                            </div>
                            <div style="font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; margin-top: 4px;">
                                Service Central des Consultations & Soins Médicaux
                            </div>
                        </td>
                        <td style="text-align: right;">
                            <span style="display: inline-block; padding: 4px 10px; font-size: 10px; font-weight: 700; color: #38bdf8; background-color: rgba(56, 189, 248, 0.15); border: 1px solid rgba(56, 189, 248, 0.3); border-radius: 6px; text-transform: uppercase;">
                                Convocation
                            </span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- Corps du Message -->
        <tr>
            <td style="padding: 32px;">
                
                <h1 style="margin: 0 0 16px 0; font-size: 18px; font-weight: 800; color: #0f172a; letter-spacing: -0.3px;">
                    Rappel de votre consultation médicale
                </h1>

                <p style="margin: 0 0 20px 0; font-size: 14px; line-height: 22px; color: #334155;">
                    {{ $salutation }} <strong>{{ $patientName }}</strong>,
                </p>

                <p style="margin: 0 0 24px 0; font-size: 13px; line-height: 22px; color: #475569;">
                    Nous vous rappelons que votre consultation médicale auprès de notre établissement est programmée pour le <strong>{{ $dateFr }} à {{ $heure }}</strong>.
                </p>

                <!-- Carte Récapitulative du Rendez-vous -->
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; margin-bottom: 24px;">
                    <tr>
                        <td style="padding: 20px;">
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td style="padding-bottom: 12px; border-bottom: 1px solid #e2e8f0;">
                                        <span style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Référence dossier</span><br>
                                        <span style="font-family: monospace; font-size: 14px; font-weight: 700; color: #0f172a;">{{ str_starts_with($rdv->reference_rdv, '#') ? $rdv->reference_rdv : '#' . $rdv->reference_rdv }}</span>
                                    </td>
                                    <td style="padding-bottom: 12px; border-bottom: 1px solid #e2e8f0; text-align: right;">
                                        <span style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Horaire fixe</span><br>
                                        <span style="font-size: 14px; font-weight: 800; color: #0284c7;">{{ $heure }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding-top: 12px; padding-bottom: 8px;">
                                        <span style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Praticien</span><br>
                                        <span style="font-size: 13px; font-weight: 700; color: #0f172a;">{{ str_starts_with($doctorName, 'Dr') ? $doctorName : 'Dr. ' . $doctorName }}</span>
                                    </td>
                                    <td style="padding-top: 12px; padding-bottom: 8px; text-align: right;">
                                        <span style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Pôle médical</span><br>
                                        <span style="font-size: 13px; font-weight: 600; color: #0f172a;">{{ $specialiteName }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" style="padding-top: 8px;">
                                        <span style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Lieu d'accueil</span><br>
                                        <span style="font-size: 12px; color: #334155;">{{ $bureau }}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                <!-- Consigne d'accueil -->
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; margin-bottom: 24px;">
                    <tr>
                        <td style="padding: 14px 16px;">
                            <div style="font-size: 11px; font-weight: 800; color: #166534; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">
                                Consigne importante d'accueil
                            </div>
                            <div style="font-size: 12px; line-height: 18px; color: #15803d;">
                                Merci de vous présenter au guichet <strong>15 minutes avant l'horaire indiqué</strong>, muni de votre pièce d'identité officielle et de vos ordonnances ou examens antérieurs pertinents.
                            </div>
                        </td>
                    </tr>
                </table>

                <!-- Bouton d'Action -->
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 24px;">
                    <tr>
                        <td align="center">
                            <a href="{{ route('patient.rendez-vous.show', $rdv->id) }}" style="display: inline-block; padding: 13px 28px; background-color: #0f172a; color: #ffffff; text-decoration: none; font-size: 13px; font-weight: 700; border-radius: 10px; letter-spacing: 0.2px;">
                                Consulter ma convocation officielle en ligne
                            </a>
                        </td>
                    </tr>
                </table>

                <p style="margin: 0; font-size: 12px; line-height: 19px; color: #64748b; text-align: center;">
                    En cas d'empêchement, vous pouvez annuler votre rendez-vous depuis votre espace patient sécurisé afin de libérer ce créneau pour un autre patient.
                </p>

            </td>
        </tr>

        <!-- Pied de page officiel -->
        <tr>
            <td style="padding: 24px 32px; background-color: #f8fafc; border-top: 1px solid #e2e8f0; text-align: center;">
                <div style="font-size: 11px; font-weight: 700; color: #0f172a; margin-bottom: 4px;">
                    HÔPITAL RDV — SYSTÈME CENTRALISÉ DE CONSULTATIONS
                </div>
                <div style="font-size: 10.5px; color: #94a3b8; line-height: 16px;">
                    Ce message automatique a été émis conformément à la réglementation sur les soins et à la Loi n°2013-450 sur la protection des données personnelles.<br>
                    Pour toute question médicale urgente, veuillez contacter directement le service d'accueil des urgences.
                </div>
            </td>
        </tr>

    </table>

</body>
</html>
