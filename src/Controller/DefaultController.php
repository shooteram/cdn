<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends AbstractController
{
    public function home(): Response
    {
        return new Response('<title>#! /usr/bin/php</title><code>hello</code>');
    }
}
