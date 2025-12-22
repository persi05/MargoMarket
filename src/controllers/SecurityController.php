<?php

require_once 'AppController.php';
require_once __DIR__ . '/../repository/UserRepository.php';

class SecurityController extends AppController
{
    private UserRepository $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }

    public function login(): void
    {
        if ($this->getCurrentUser()) {
            $this->redirect('/');
        }

        if (!$this->isPost()) {
            $this->render('auth/login');
            return;
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $this->render('auth/login', [
                'messages' => 'Wypełnij wszystkie pola',
                'email' => $email
            ]);
            return;
        }

        $user = $this->userRepository->getUserByEmail($email);

        if (!$user) {
            $this->render('auth/login', [
                'messages' => 'Nieprawidłowy email lub hasło',
                'email' => $email
            ]);
            return;
        }

        if (!$this->userRepository->verifyPassword($user, $password)) {
            $this->render('auth/login', [
                'messages' => 'Nieprawidłowy email lub hasło',
                'email' => $email
            ]);
            return;
        }

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