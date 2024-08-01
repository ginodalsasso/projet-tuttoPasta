<?php

namespace App\Domain\AntiSpam\Puzzle;

use App\Domain\AntiSpam\ChallengeGenerator;
use Intervention\Image\ImageManager;
use Symfony\Component\HttpFoundation\Response;

// Définition de la classe PuzzleGenerator qui implémente l'interface ChallengeGenerator
class PuzzleGenerator implements ChallengeGenerator
{
    // Constructeur qui injecte une instance de PuzzleChallenge en lecture seule
    public function __construct(private readonly PuzzleChallenge $challenge)
    {
    }

    
    // Génère une réponse HTTP contenant une image de puzzle
    public function generate(string $key): Response
    {
        // Récupère la solution (position) pour la clé donnée
        $position = $this->challenge->getSolution($key);
        if (!$position) {
            // Retourne une réponse 404 si la solution n'est pas trouvée
            return new Response(null, 404);
        }

        // Déstructure la position en coordonnées x et y
        [$x, $y] = $position;
        // Définit les chemins des images de fond et de la pièce du puzzle
        $backgroundPath = sprintf('%s/captcha.webp', __DIR__);
        $piecePath = sprintf('%s/piece.png', __DIR__);

        // Crée un gestionnaire d'images avec le driver 'gd'
        $manager = new ImageManager(['driver' => 'gd']);
        // Charge les images de fond et de la pièce du puzzle
        $image = $manager->make($backgroundPath);
        $piece = $manager->make($piecePath);
        // Clone l'image de la pièce pour créer le trou
        $hole = clone $piece;
        // Insère l'image de fond dans la pièce du puzzle pour masquer les bords
        $piece->insert($image, 'top-left', -$x, -$y)
            ->mask($hole, true);
        // Redimensionne le canvas de l'image de fond pour accueillir la pièce du puzzle
        $image
            ->resizeCanvas(
                PuzzleChallenge::PIECE_WIDTH,
                0,
                'left',
                true,
                'rgba(0, 0, 0, 0)'
            )
            // Insère la pièce du puzzle à droite de l'image de fond
            ->insert($piece, 'top-right')
            // Insère le trou avec une opacité de 80% à la position x et y
            ->insert($hole->opacity(80), 'top-left', $x, $y);

        // Retourne l'image générée sous forme de réponse HTTP au format PNG
        return $image->response('png');
    }
}
