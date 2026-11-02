<?php

namespace App\Contracts;

interface PushSender
{
    /**
     * @param  list<string>  $tokens
     * @return list<string> Invalid or expired tokens that should be removed.
     */
    public function send(array $tokens, string $title, string $body, string $url): array;
}
