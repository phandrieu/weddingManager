<?php

namespace App\Enum;

enum CelebrationPeriod: string
{
    case accueil = 'Accueil';
    case liturgieDeLaParole = 'Liturgie de la Parole';
    case liturgieDuMariage = 'Liturgie du Mariage';
    case liturgieEucharistique = 'Liturgie Eucharistique';
    case envoi = 'Envoi';
}