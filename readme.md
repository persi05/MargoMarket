# **🎮 Margonem Market**

Platforma handlowa dla graczy gry Margonem - umożliwia wystawianie i przeglądanie ogłoszeń sprzedaży przedmiotów z gry.

<br>
<br>

## **Baza danych-ERD**
<img width="1705" height="1595" alt="diagramERD" src="https://github.com/user-attachments/assets/4f157e4b-bde1-45c9-803d-0b8e8469b218" />

<br>
<br>

**Tabele główne:**

users - Użytkownicy systemu
<br>
listings - Ogłoszenia sprzedaży
<br>
roles - Role użytkowników (user, admin)
<br>
listing_statuses - Statusy ogłoszeń (active, sold)
<br>
user_sessions - Sesje użytkowników
<br>
listing_favorites - Ulubione ogłoszenia użytkowników

<br>

**Tabele słownikowe:**

servers - Serwery gry (np. Aldous, Belagor)
<br>
item_types - Typy przedmiotów (broń, zbroja, etc.)
<br>
rarities - Rzadkości przedmiotów (unikalne, heroiczne)
<br>
currencies - Waluty (PLN, złoto w grze)

<br>

**Widoki:**

search_listings - Funkcja wyszukiwania ogłoszeń z filtrami
<br>
user_favorites_view - Widok ulubionych ogłoszeń użytkownika

<br>

**Funkcje:**

mark_listing_as_sold() - Oznaczanie ogłoszenia jako sprzedane
<br>
search_listings() - Zaawansowane wyszukiwanie z paginacją

<br>

**Wyzwalacze:**

update_listing_sold_at_trigger - Automatyczne ustawianie daty sprzedaży
<br>
<br>

## **Screeny aplikacji**
Dla pokazaniu na zdjęciu całości strony, dla niektórych screenów ustawiono powiększenie strony na 90%. Dla użytkowników dostępny jest **scrollbar**.

<br>

**Widok logowania**
<img width="1916" height="906" alt="logowanie" src="https://github.com/user-attachments/assets/f9630299-b2b0-409f-b1c8-511096bf7fdb" />

<br>

**Widok rejestracji**
<img width="1917" height="904" alt="rejestracja" src="https://github.com/user-attachments/assets/ae86ab0f-7faa-4fa8-80bb-58575ad66f4e" />

<br>

**Widok głowny z ogłoszeniami**
<img width="1919" height="911" alt="ogloszenia" src="https://github.com/user-attachments/assets/9f5e1cf5-de42-41ad-ac67-84322198b324" />

<br>

**Widok swoich ogłoszeń**
<img width="1919" height="902" alt="moje_ogloszenia" src="https://github.com/user-attachments/assets/14ecab8a-1dd3-4d7d-bb8d-0172dc481f52" />

<br>

**Widok polubionych ogłoszeń**
<img width="1902" height="895" alt="ulubione" src="https://github.com/user-attachments/assets/09662450-1b89-45cd-ba21-05dfcbeb4391" />

<br>

**Widok tworzenia ogłoszenia**
<img width="1919" height="911" alt="stworz" src="https://github.com/user-attachments/assets/de096ee2-c04c-4c8d-ad29-fba13e56e982" />

<br>

**Widok admina z moderacją ogłoszeń**
<img width="1897" height="913" alt="admin_glowny" src="https://github.com/user-attachments/assets/b106d5a4-ed48-46c1-b198-7361f0dc0f4c" />

<br>

**Widok admina z zarządzaniem użytkownikami**
<img width="1919" height="905" alt="admin_users" src="https://github.com/user-attachments/assets/6ca9fb4a-c703-488b-b30b-183fbbf84504" />

<br>
<br>

## **Architektura**

<img width="593" height="916" alt="warstwy" src="https://github.com/user-attachments/assets/48fb511f-4ed0-4047-b73d-78f3acfb0a91" />


<br>
<br>

## **Instrukcja uruchomienia**

### Wymagania:
- Docker Desktop (lub Docker + Docker Compose)
- Git

### Krok 1: Klonowanie repozytorium
```bash
git clone https://github.com/persi05/MargoMarket.git
cd margonem-market
```

### Krok 2: Konfiguracja zmiennych środowiskowych
```bash
cp .env.example .env
```

Edytuj plik `.env` i uzupełnij dane

### Krok 3: Generowanie certyfikatu SSL (self-signed)
```bash
mkdir -p docker/nginx/ssl
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout docker/nginx/ssl/nginx-selfsigned.key \
  -out docker/nginx/ssl/nginx-selfsigned.crt \
  -subj "/C=PL/ST=Silesia/L=Bystra/O=MargonemMarket/CN=localhost"
```

### Krok 4: Uruchomienie kontenerów
```bash
docker compose up -d
```

### Krok 5: Inicjalizacja bazy danych

- Skopiuj dump sql do pgAdmin


### Krok 6: Dostęp do aplikacji

- **Aplikacja**: https://localhost:8443
- **pgAdmin**: http://localhost:5050


## 🔑 Zmienne środowiskowe

Plik `.env.example` zawiera wszystkie wymagane zmienne:

**Zmienna**    -  **Opis**
    
`DB_HOST` - Host bazy danych

`DB_NAME`- Nazwa bazy danych

`DB_USER`   - Użytkownik bazy danych

`DB_PASSWORD` - Hasło do bazy danych

`POSTGRES_USER` - Użytkownik PostgreSQL

`POSTGRES_PASSWORD` -  Hasło PostgreSQL

`POSTGRES_DB`    - Nazwa bazy PostgreSQL

`SESSION_LIFETIME` - Czas życia sesji (sekundy)


## Scenariusze testowe

### Scenariusz 1: Rejestracja i logowanie

**Cel:** Weryfikacja systemu autentykacji

**Kroki:**
1. Otwórz https://localhost:8443
2. Kliknij "Zarejestruj się"
3. Wprowadź dane:
   - Email: `test@example.com`
   - Hasło: `test123`
   - Powtórz hasło: `test123`
4. Kliknij "Zarejestruj się"

**Oczekiwany rezultat:** ✅
- Przekierowanie na stronę logowania
- Komunikat: "Konto utworzone! Możesz się teraz zalogować."

**Kroki:**
5. Zaloguj się używając utworzonych danych
6. Sprawdź czy jesteś na stronie głównej (`/`)
7. Sprawdź czy widać przycisk "Wyloguj" w headerze

**Oczekiwany rezultat:** ✅
- Przekierowanie na stronę główną
- Wyświetlenie nawigacji dla zalogowanych użytkowników

---

### Scenariusz 2: Role użytkowników (User vs Admin)

**Cel:** Weryfikacja systemu ról

#### Część A: Konto zwykłego użytkownika

**Kroki:**
1. Zaloguj się jako zwykły użytkownik (`test@example.com`)
2. Spróbuj wejść na `/admin`

**Oczekiwany rezultat:** ✅
- Strona błędu 403 (Brak dostępu)
- Komunikat: "Brak dostępu. Zaloguj jako admin"

#### Część B: Konto administratora

**Kroki:**
1. W bazie danych zmień rolę użytkownika na admin:
```sql
UPDATE users 
SET role_id = (SELECT id FROM roles WHERE name = 'admin')
WHERE email = 'test@example.com';
```
2. Wyloguj się i zaloguj ponownie
3. Spróbuj wejść na `/admin`

**Oczekiwany rezultat:** ✅
- Dostęp do panelu administratora
- Wyświetlenie statystyk i tabel zarządzania

---

### Scenariusz 3: CRUD - Ogłoszenia (Create, Read, Update, Delete)

**Cel:** Weryfikacja operacji na ogłoszeniach

#### CREATE - Tworzenie ogłoszenia

**Kroki:**
1. Zaloguj się jako użytkownik
2. Kliknij "Stwórz ogłoszenie" w menu
3. Wypełnij formularz:
   - Nazwa: `Miecz Ognia +5`
   - Typ: `Broń jednoręczna`
   - Rzadkość: `Heroiczne`
   - Poziom: `100`
   - Cena: `50000`
   - Waluta: `w grze`
   - Świat: `Aldous`
   - Kontakt: `testuser#1234`
4. Kliknij "Opublikuj ogłoszenie"

**Oczekiwany rezultat:** ✅
- Przekierowanie na `/my-listings`
- Komunikat: "Ogłoszenie zostało utworzone!"
- Nowe ogłoszenie widoczne na liście

#### READ - Odczyt ogłoszeń

**Kroki:**
1. Przejdź na stronę główną (`/`)
2. Sprawdź czy nowo utworzone ogłoszenie jest widoczne w tabeli
3. Użyj filtrów:
   - Wpisz "Miecz" w wyszukiwarkę
   - Wybierz serwer "Aldous"
   - Ustaw poziom min: 50, max: 150
4. Kliknij "Filtruj"

**Oczekiwany rezultat:** ✅
- Ogłoszenie jest widoczne w tabeli
- Filtry działają poprawnie
- Wyświetla się tylko ogłoszenie spełniające kryteria

#### UPDATE - Oznaczanie jako sprzedane

**Kroki:**
1. Przejdź do "Moje ogłoszenia"
2. Znajdź utworzone ogłoszenie
3. Kliknij przycisk "Sprzedane"

**Oczekiwany rezultat:** ✅
- Komunikat: "Ogłoszenie oznaczone jako sprzedane!"
- Status ogłoszenia zmienia się na "SPRZEDANE"
- Ogłoszenie znika ze strony głównej
- Przycisk "Sprzedane" znika
- Data sprzedaży zostaje automatycznie ustawiona (wyzwalacz)

#### DELETE - Usuwanie ogłoszenia

**Kroki:**
1. Zostań na stronie "Moje ogłoszenia"
2. Przy aktywnym ogłoszeniu kliknij ikonę kosza
3. Potwierdź usunięcie w dialogu

**Oczekiwany rezultat:** ✅
- Komunikat: "Ogłoszenie zostało usunięte!"
- Ogłoszenie znika z listy
- Ogłoszenie nie jest już widoczne na stronie głównej

---

### Scenariusz 4: Błędy HTTP i autoryzacja

**Cel:** Weryfikacja stron błędów i zabezpieczeń dostępu

<br>

#### Test 1: 400 Bad Request - Nieprawidłowe żądanie

**Kroki:**
1. Spróbuj wysłać request z błędnymi parametrami:
```bash
curl -X POST https://localhost:8443/delete-listing \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "listing_id=invalid_value"
```

**Oczekiwany rezultat:** ✅
- Status HTTP: `400 Bad Request`
- Wyświetlenie strony `public/views/400.html`
- Komunikat: "Nieprawidłowe żądanie"
- Przycisk powrotu do strony głównej

<br>

#### Test 3: 403 Forbidden - Brak uprawnień

**Kroki:**
1. Zaloguj się jako zwykły użytkownik (nie admin)
2. Spróbuj wejść na `/admin`

**Oczekiwany rezultat:** ✅
- Status HTTP: `403 Forbidden`
- Wyświetlenie strony `public/views/403.html`
- Komunikat: "Brak dostępu. Zaloguj jako admin"
- Ikona kłódki
- Przycisk powrotu do strony głównej

<br>

#### Test 4: 404 Not Found - Nieistniejąca strona

**Kroki:**
1. Wejdź na nieistniejący URL: `https://localhost:8443/japidi`

**Oczekiwany rezultat:** ✅
- Status HTTP: `404 Not Found`
- Wyświetlenie strony `public/views/404.html`
- Komunikat: "Strona nie została znaleziona"
- Ikona search_off
- Przycisk powrotu do strony głównej

<br>

#### Test 5: 405 Method Not Allowed - Niewłaściwa metoda HTTP

**Kroki:**
1. Spróbuj wysłać GET zamiast POST do endpointu wymagającego POST:
```bash
curl -X GET https://localhost:8443/delete-listing?listing_id=1
```

**Oczekiwany rezultat:** ✅
- Redirect na odpowiednią stronę lub ignorowanie żądania
- Wyświetlenie strony `public/views/405.html` (w odpowiednich przypadkach)
- Komunikat: "Metoda niedozwolona"

<br>

### Scenariusz 5: Widoki i funkcje bazy danych

**Cel:** Weryfikacja widoków i funkcji PostgreSQL

#### Test 1: Funkcja search_listings()

**Kroki:**
1. Stwórz kilka ogłoszeń z różnymi parametrami
2. Wykonaj w pgAdmin:
```sql
SELECT * FROM search_listings(
    'Miecz',      -- search_term
    1,            -- server_id
    50,           -- min_level
    150,          -- max_level
    1,            -- item_type_id
    NULL,         -- rarity_id
    NULL,         -- currency_id
    10,           -- limit
    0             -- offset
);
```

**Oczekiwany rezultat:** ✅
- Zwraca tylko ogłoszenia spełniające kryteria
- Zawiera wszystkie kolumny z JOIN'ami

#### Test 2: Widok user_favorites_view

**Kroki:**
1. Dodaj ogłoszenie do ulubionych (gwiazdka na stronie głównej)
2. Wykonaj w pgAdmin:
```sql
SELECT * FROM user_favorites_view 
WHERE user_id = 1; -- ID zalogowanego użytkownika
```

**Oczekiwany rezultat:** ✅
- Wyświetla ulubione ogłoszenia użytkownika
- Zawiera wszystkie szczegóły ogłoszenia + datę dodania do ulubionych

#### Test 3: Funkcja mark_listing_as_sold()

**Kroki:**
1. Wykonaj w pgAdmin:
```sql
SELECT mark_listing_as_sold(1, 1); -- listing_id, user_id
```
2. Sprawdź status ogłoszenia:
```sql
SELECT * FROM listings WHERE id = 1;
```

**Oczekiwany rezultat:** ✅
- Status zmienia się na 'sold'
- `sold_at` automatycznie ustawione (wyzwalacz)
- Funkcja zwraca TRUE

---

### Scenariusz 6: Wyzwalacze (Triggers)

**Cel:** Weryfikacja automatycznych akcji w bazie danych

#### Test: update_listing_sold_at_trigger

**Kroki:**
1. Stwórz nowe ogłoszenie (status: active, sold_at: NULL)
2. Zmień status na 'sold':
```sql
UPDATE listings 
SET status_id = (SELECT id FROM listing_statuses WHERE name = 'sold')
WHERE id = 1;
```
3. Sprawdź pole `sold_at`:
```sql
SELECT id, status_id, sold_at FROM listings WHERE id = 1;
```

**Oczekiwany rezultat:** ✅
- Pole `sold_at` automatycznie wypełnione aktualnym timestampem
- Timestamp ustawiony w momencie zmiany statusu

---

### Scenariusz 7: Ulubione ogłoszenia

**Cel:** Weryfikacja funkcjonalności ulubionych

**Kroki:**
1. Zaloguj się jako użytkownik
2. Na stronie głównej kliknij gwiazdkę przy ogłoszeniu
3. Sprawdź powiadomienie: "Dodano do ulubionych"
4. Kliknij ponownie gwiazdkę
5. Sprawdź powiadomienie: "Usunięto z ulubionych"
6. Dodaj kilka ogłoszeń do ulubionych
7. Przejdź do zakładki "Ulubione" w menu
8. Sprawdź czy wszystkie dodane ogłoszenia są widoczne
9. Kliknij "Usuń z ulubionych" przy wybranym ogłoszeniu

**Oczekiwany rezultat:** ✅
- AJAX dodaje/usuwa ulubione bez przeładowania strony
- Gwiazdka zmienia kolor (szara → żółta)
- Powiadomienia pojawiają się na 3 sekundy
- Strona "Ulubione" wyświetla wszystkie ulubione ogłoszenia
- Usuwanie z ulubionych działa poprawnie

---

### Scenariusz 8: Panel administratora

**Cel:** Weryfikacja funkcji administratora

**Kroki:**
1. Zaloguj się jako admin
2. Wejdź na `/admin`
3. Sprawdź statystyki (liczba ogłoszeń, użytkowników, stron)
4. Użyj filtrów (wyszukiwanie, serwer, status)
5. Usuń dowolne ogłoszenie
6. Przejdź do zakładki "Użytkownicy"
7. Sprawdź listę użytkowników z statystykami
8. Spróbuj usunąć własne konto
9. Spróbuj usunąć inne konto

**Oczekiwany rezultat:** ✅
- Statystyki wyświetlają poprawne liczby
- Filtry działają (tylko admin widzi wszystkie statusy)
- Admin może usunąć dowolne ogłoszenie
- Lista użytkowników pokazuje statystyki (liczba ogłoszeń)
- Nie można usunąć własnego konta (błąd)
- Nie można usunąć ostatniego admina (błąd)
- Można usunąć zwykłych użytkowników

---

## ✅ Checklist funkcjonalności

### 🔐 Autoryzacja i sesje
- Rejestracja użytkowników z walidacją
- Logowanie z hashowaniem haseł (bcrypt)
- System sesji z tokenami
- Automatyczne czyszczenie wygasłych sesji
- CSRF protection w formularzach
- Wymuszenie HTTPS
- Weryfikacja sesji przy każdym żądaniu
- Wylogowanie z usunięciem sesji

### 👥 System ról
- Role: user, admin
- Dostęp do panelu admin tylko dla adminów
- Strony błędów 403 (Forbidden)
- Ochrona przed usunięciem własnego konta admina
- Ochrona przed usunięciem ostatniego admina

### 📝 CRUD Ogłoszeń
- Tworzenie ogłoszeń (tylko zalogowani)
- Wyświetlanie wszystkich aktywnych ogłoszeń
- Filtrowanie i wyszukiwanie (nazwa, serwer, poziom, typ, rzadkość, waluta)
- Paginacja wyników (50 na stronę)
- Oznaczanie jako sprzedane
- Usuwanie własnych ogłoszeń(tylko niesprzedanych)
- Walidacja danych wejściowych
- Ochrona przed SQL Injection (prepared statements)

### ⭐ Ulubione
- Dodawanie do ulubionych
- Usuwanie z ulubionych
- Podgląd ulubionych ogłoszeń
- Powiadomienia po akcjach
- Animacje przycisków

### 🔍 Wyszukiwanie i filtrowanie
- Wyszukiwanie po nazwie (AJAX)
- Filtrowanie po serwerze
- Filtrowanie po zakresie poziomów
- Filtrowanie po typie przedmiotu
- Filtrowanie po rzadkości
- Filtrowanie po walucie
- Łączenie wielu filtrów
- Paginacja wyników wyszukiwania

### 👨‍💼 Panel Administratora
- Dashboard ze statystykami
- Zarządzanie ogłoszeniami (wszystkie statusy)
- Usuwanie dowolnych ogłoszeń
- Zarządzanie użytkownikami
- Wyświetlanie statystyk użytkowników
- Usuwanie użytkowników
- Filtrowanie ogłoszeń (search, server, status)
- Paginacja w panelu admina

### 🗄️ Baza danych
- PostgreSQL 17
- 13 tabel (users, listings, roles, etc.)
- Foreign keys z CASCADE
- Indeksy na kluczach obcych
- 2 widoki (search_listings, user_favorites_view)
- 1 funkcję (search_listings)
- 1 transakcję (mark_listing_as_sold)
- 1 wyzwalacz (update_listing_sold_at_trigger)

### 🎨 Frontend
- Responsywny design (mobile, tablet, desktop)
- Dark theme z gradientami
- Animacje i przejścia
- Material Icons
- Interaktywne formularze
- AJAX requests (Fetch API)
- Powiadomienia toast
- Mobile navigation
- CSS Variables
- Modularny CSS (common.css + specific)

### 🔒 Bezpieczeństwo
- Password hashing (bcrypt)
- SQL Injection prevention (PDO prepared statements)
- XSS prevention (htmlspecialchars)
- CSRF tokens
- HTTPS only
- Secure session cookies (httponly, secure, samesite)
- Session expiration
- Role-based access control
- Input validation (długość, typ, zakres)

### 📱 UX/UI
- Loading states
- Empty states
- Error pages (400, 403, 404, 405, 500)
- Success/error alerts
- Confirmation dialogs
- Responsive tables
- Smooth animations

### ⚡ Optymalizacje
- Paginacja (limit/offset)
- Database indexes
- Lazy loading SQL
- Repository pattern (singleton)


persii05 :*
