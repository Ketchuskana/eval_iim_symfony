<?php

namespace App\Message;

final class SendMessage
{
private int $userId;
    private string $label;

    public function __construct(int $userId, string $label)
    {
        $this->userId = $userId;
        $this->label = $label;
    }

    public function getUserId(): int { return $this->userId; }
    public function getLabel(): string { return $this->label; }
}
