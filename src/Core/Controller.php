<?php

namespace Core;
use App\Models\User;

class Controller
{
    public User|null $user;
    public string $homeDirectory;
    public function __construct(User|null $user) {
        $this->user = $user;
        $this->homeDirectory = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
    }

    protected function view(string $view, array $data = []): void
    {
        extract($data);
        $user = $this->user;
        $homeDir = $this->homeDirectory;
        require_once __DIR__ . '/../App/Views/' . $view . '.php';
    }
}

?>