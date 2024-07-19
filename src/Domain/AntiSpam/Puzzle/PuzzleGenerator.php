<?php

namespace App\Domain\AntiSpam\Puzzle;

use Intervention\Image\ImageManager;
use App\Domain\AntiSpam\ChanllengeGenerator;
use Symfony\Component\HttpFoundation\Response;

class PuzzleGenerator implements ChanllengeGenerator
{

    public function __construct(private readonly PuzzleChallenge $challenge){

    }

    public function generate(string $key): Response
    {
        $position = $this->challenge->getSolution($key);

        if(!$position) {
            return new Response(null, 404);
        }

        [$x, $y] = $position;
        $backgroundPath = sprintf('%s/captcha.webp', __DIR__);
        $piecePath = sprintf('%s/piece.png', __DIR__);

        $manager = new ImageManager(['driver' => 'gd']);
        $image = $manager->make($backgroundPath);
        $piece = $manager->make($piecePath);
        $hole = clone $piece;
        $piece->insert($image,'top-left', -$x, -$y)
            ->mask($hole, true);
        $image
            ->resizeCanvas(
                PuzzleChallenge::PIECE_WIDTH,
                0,
                'left',
                true,
                'rgba(0,0,0,0)'
            )
            ->insert($piece,'top-right')
            ->insert($hole->opacity(60), 'top-left', $x, $y);

        return $image->response('webp');
    }
}