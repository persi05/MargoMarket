<?php

require_once 'AppController.php';
require_once __DIR__ . '/../repository/UserRepository.php';

class SecurityController extends AppController
{
    private UserRepository $userRepository;

    public function __construct()
    {
        $this->userRepository = UserRepository::getInstance();
    }

    public function login(): void
    {
        $this->requireHttps();

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        if ($this->getCurrentUser()) {
            $this->redirect('/');
        }

        if (!$this->isPost()) {
            $this->render('auth/login', [
                'csrf_token' => $_SESSION['csrf_token']
            ]);
            return;
        }

        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $this->render('auth/login', [
                'messages' => 'Błąd bezpieczeństwa (CSRF). Odśwież stronę i spróbuj ponownie.',
                'csrf_token' => $_SESSION['csrf_token']
            ]);
            return;
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $this->render('auth/login', [
                'messages' => 'Wypełnij wszystkie pola',
                'email' => $email,
                'csrf_token' => $_SESSION['csrf_token']
            ]);
            return;
        }

        $user = $this->userRepository->getUserByEmail($email);

        if (!$user) {
            $this->render('auth/login', [
                'messages' => 'Nieprawidłowy email lub hasło',
                'email' => $email,
                'csrf_token' => $_SESSION['csrf_token']
            ]);
            return;
        }

        if (!$this->userRepository->verifyPassword($user, $password)) {
            $this->render('auth/login', [
                'messages' => 'Nieprawidłowy email lub hasło',
                'email' => $email,
                'csrf_token' => $_SESSION['csrf_token']
            ]);
            return;
        }

        session_regenerate_id(true);

        $sessionToken = bin2hex(random_bytes(32));
        
        $this->userRepository->createSession($user->getId(), $sessionToken, 3600);
        
        $_SESSION['user_id'] = $user->getId();
        $_SESSION['user_email'] = $user->getEmail();
        $_SESSION['user_role'] = $user->getRoleName();
        $_SESSION['session_token'] = $sessionToken;

        $this->redirect('/');
    }

    public function register(): void
    {
        $this->requireHttps();

        if ($this->getCurrentUser()) {
            $this->redirect('/');
        }

        if (!$this->isPost()) {
            $this->render('auth/register');
            return;
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (strlen($email) > 255) {
            $this->render('auth/register', [
                'messages' => 'Adres email jest zbyt długi (maksymalnie 255 znaków)',
                'email' => $email
            ]);
            return;
        }

        if (strlen($password) > 128) {
            $this->render('auth/register', [
                'messages' => 'Hasło jest zbyt długie (maksymalnie 128 znaków)',
                'email' => $email
            ]);
            return;
        }

        if (empty($email) || empty($password) || empty($confirmPassword)) {
            $this->render('auth/register', [
                'messages' => 'Wypełnij wszystkie pola',
                'email' => $email
            ]);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->render('auth/register', [
                'messages' => 'Nieprawidłowy adres email',
                'email' => $email
            ]);
            return;
        }

        if (strlen($password) < 6) {
            $this->render('auth/register', [
                'messages' => 'Hasło musi mieć minimum 6 znaków',
                'email' => $email
            ]);
            return;
        }

        if ($password !== $confirmPassword) {
            $this->render('auth/register', [
                'messages' => 'Hasła nie są identyczne',
                'email' => $email
            ]);
            return;
        }

        if ($this->userRepository->emailExists($email)) {
            $this->render('auth/register', [
                'messages' => 'Ten email jest już zarejestrowany',
                'email' => $email
            ]);
            return;
        }

        $userId = $this->userRepository->createUser($email, $password);

        if (!$userId) {
            $this->render('auth/register', [
                'messages' => 'Błąd podczas rejestracji. Spróbuj ponownie.',
                'email' => $email
            ]);
            return;
        }

        $this->render('auth/login', [
            'success' => 'Konto utworzone! Możesz się teraz zalogować.',
            'email' => $email
        ]);
    }

    public function logout(): void
    {        
        if (isset($_SESSION['session_token'])) {
            $this->userRepository->deleteSessionByToken($_SESSION['session_token']);
        }
        
        session_unset();
        session_destroy();

        $this->redirect('/login');
    }
}