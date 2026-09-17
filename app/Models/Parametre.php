<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Parametre extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom_hopital',
        'slogan',
        'adresse',
        'telephone',
        'email_contact',
        'duree_creneau_defaut',
        'heure_ouverture',
        'heure_fermeture',
        'logo_path',
    ];

    /**
     * Récupérer les réglages uniques de l'établissement
     */
    public static function getSettings(): self
    {
        $settings = self::find(1);
        if (!$settings) {
            $settings = self::firstOrCreate(
                ['id' => 1],
                [
                    'nom_hopital' => 'Centre Hospitalier Universitaire',
                    'slogan' => 'Excellence médicale & Prise en charge humaine',
                    'adresse' => 'Avenue de l\'Hôpital, Douala, Cameroun',
                    'telephone' => '+237 600 00 00 00',
                    'email_contact' => 'contact@hopital-rdv.cm',
                    'duree_creneau_defaut' => 30,
                    'heure_ouverture' => '08:00',
                    'heure_fermeture' => '18:00',
                ]
            );
        }
        return $settings;
    }

    /**
     * Réinitialiser le cache si nécessaire
     */
    public static function clearCache(): void
    {
        Cache::forget('hopital_parametres');
    }
}

