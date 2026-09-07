# Hello World

Lern-Modul für IP-Symcon. Zeigt Properties, Variablen, Timer, Aktionen und Übersetzungen.

## Funktionsumfang

- Konfigurierbarer Gruß (`Greeting`) und `Name`
- Variablen: `Message` (String), `Counter` (Integer), `Active` (Boolean, schaltbar)
- Optionaler Timer, der den Gruß automatisch wiederholt

## Konfiguration

| Feld     | Beschreibung                                         |
| -------- | ---------------------------------------------------- |
| Gruß     | Erster Teil der Nachricht, Standard `Hello`          |
| Name     | Zweiter Teil der Nachricht, Standard `World`         |
| Intervall| Sekunden zwischen automatischen Aufrufen, `0` = aus  |

## PHP-Befehlsreferenz

```php
// Schreibt "<Gruß>, <Name>!" in die Variable Message, erhöht Counter, gibt den Text zurück
string HW_SayHello(int $InstanzID);

// Setzt Counter auf 0
void HW_ResetCounter(int $InstanzID);
```

## Installation

Modul-Store → Modul-Control → Repository-URL dieses Repos hinzufügen,
anschließend `Instanz hinzufügen` → `Hello World`.
