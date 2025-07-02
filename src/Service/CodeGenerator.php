<?php


namespace App\Service;

use Random\RandomException;

class CodeGenerator
{
    private const LENGTH = 3;
    /**
     * @throws RandomException
     */
    public function generate(): string
    {
        return bin2hex(random_bytes(self::LENGTH));
    }
}

