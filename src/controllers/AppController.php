<?php

class AppController
{
    protected function isGet(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function requireHttps(): void
    {
        $isHttps = (
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') || $_SERVER['SERVER_PORT'] == 443 
        );

        if (!$isHttps) {
            http_response_code(403);
            include 'public/views/403.html';

        }
    }

    protected function render(string $template = null, array $variables = []): void
    {
        $templatePath = 'public/views/'.$template.'.html';
        
        if (file_exists($templatePath)) {
            extract($variables);
            
            ob_start();
            include $templatePath;
            $output = ob_get_clean();
            echo $output;
        } else {
            http_response_code(404);
            include 'public/views/404.html';
        }
    }

    protected function redirect(string $path): void
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $url = "{$protocol}://{$_SERVER['HTTP_HOST']}{$path}";
        header("Location: {$url}");
        exit();
    }

    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }

    protected function getCurrentUser(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    protected function requireAuth(): void
    {
        if (!$this->getCurrentUser()) {
            $this->redirect('/login');
        }
    }

    protected function requireAdmin(): void
    {
        $this->requireAuth();
        
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            http_response_code(403);
            die('Access denied');
        }
    }
}