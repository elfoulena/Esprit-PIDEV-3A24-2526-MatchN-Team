<?php
namespace App\Service;

class PrioriteReclamationService
{
    private const CRITIQUE = [
        'harcèlement','harcelé','harceler','agression','agressé','violence',
        'violent','menace','menacé','intimidation','intimidé','discrimination',
        'discriminé','accident','blessure','blessé','urgence','danger','dangereux',
        'avocat','tribunal','plainte','procès','poursuite','juridique','illégal',
        'fraude','détournement','corruption','licenciement abusif','renvoi injuste',
        'révocation abusive',
    ];

    private const HAUTE = [
        'paiement','salaire','prime','virement','remboursement','indemnité',
        'compensation','impayé','retenue','cotisation','augmentation refusée',
        'contrat','rupture','licenciement','démission','congé','maternité',
        'paternité','heures supplémentaires','surcharge','surmenage','épuisement',
        'burnout','burn-out','évaluation injuste','discrimination salariale',
        'inégalité','favoritisme','traitement inégal',
    ];

    private const MOYENNE = [
        'problème','erreur','faute','dysfonctionnement','conflit','dispute',
        'désaccord','tension','ambiance','atmosphère','relation','conditions',
        'environnement','bureau','matériel','équipement','formation','manque',
        'absence','retard','ponctualité','communication','information',
        'incompréhension','malentendu','ignoré','non répondu',
    ];

    private function normaliser(string $texte): string
    {
        $texte = mb_strtolower($texte, 'UTF-8');
        $map = [
            'à'=>'a','â'=>'a','ä'=>'a','é'=>'e','è'=>'e','ê'=>'e','ë'=>'e',
            'î'=>'i','ï'=>'i','ô'=>'o','ö'=>'o','ù'=>'u','û'=>'u','ü'=>'u',
            'ç'=>'c','æ'=>'ae','œ'=>'oe','ñ'=>'n',
        ];
        return strtr($texte, $map);
    }

    public function calculer(string $message): array
    {
        $texte = $this->normaliser($message);

        foreach ([
            4 => self::CRITIQUE,
            3 => self::HAUTE,
            2 => self::MOYENNE,
        ] as $score => $motsCles) {
            foreach ($motsCles as $mot) {
                if (str_contains($texte, $this->normaliser($mot))) {
                    return [
                        'score'  => $score,
                        'niveau' => match($score) {
                            4 => 'CRITIQUE',
                            3 => 'HAUTE',
                            2 => 'MOYENNE',
                        },
                    ];
                }
            }
        }

        return ['score' => 1, 'niveau' => 'NORMALE'];
    }
}