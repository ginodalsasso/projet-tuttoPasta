<?php

namespace App\Domain\AntiSpam;

interface ChallengeInterface
{

    public function generateKey(): string;

    public function verify(string $key, string $anwser): bool;

    public function getSolution(string $key): mixed;

}
