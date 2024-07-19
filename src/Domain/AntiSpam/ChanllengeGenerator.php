<?php

namespace App\Domain\AntiSpam;

use Symfony\Component\HttpFoundation\Response;

interface ChanllengeGenerator
{

    public function generate(string $key): Response;


}