# GLPI - Integracja z Trello

Plugin umożliwiający integrację GLPI z Trello - tworzenie i zarządzanie kartami Trello bezpośrednio z GLPI.

## Funkcje

- Tworzenie kart Trello bezpośrednio z zgłoszeń GLPI
- Konfiguracja połączenia z API Trello
- Wybór tablicy i listy docelowej w Trello
- Pełna integracja z interfejsem GLPI
- Wsparcie dla wielu języków

## Wymagania

- GLPI w wersji 10.0.0 - 10.0.99
- PHP 7.4 lub nowszy
- Konto Trello z dostępem do API

## Instalacja

1. Pobierz plugin i rozpakuj go do katalogu `plugins` w instalacji GLPI
2. Zmień nazwę katalogu na `trello`
3. Przejdź do menu Konfiguracja > Plugins w GLPI
4. Zainstaluj i aktywuj plugin

## Konfiguracja

1. Przejdź do menu Konfiguracja > Plugins > Trello
2. Wprowadź wymagane dane:
   - Klucz API Trello
   - Token API Trello
   - ID tablicy Trello
   - ID listy Trello
3. Zapisz konfigurację

## Jak używać

1. Otwórz zgłoszenie w GLPI
2. Kliknij przycisk "Wyślij do Trello"
3. Potwierdź utworzenie karty

## Wsparcie

W przypadku problemów lub pytań:
- Utwórz zgłoszenie w repozytorium GitHub
- Skontaktuj się z autorem przez stronę projektu

## Licencja

Ten plugin jest dystrybuowany na licencji GNU General Public License v2 lub nowszej.

## Autor

My-it.pl - Paweł Adamczuk
https://my-it.pl 