<?php
// app/Helpers/DateHelper.php
namespace App\Helpers;

class DateHelper
{
    /**
     * Formate une date SQL (YYYY-MM-DD ou DATETIME) en français :
     * ex. "2025-07-03 14:05:00" → "03 juillet 2025 à 14h05"
     */
    public static function formatFr(string $raw): string
    {
        $ts = strtotime($raw);
        if ($ts === false) {
            return $raw;
        }
        $day   = date('d', $ts);
        $month = (int)date('n', $ts);
        $year  = date('Y', $ts);
        $hour  = date('H', $ts);
        $min   = date('i', $ts);

        $months = [
            1  => 'janvier',
            2  => 'février',
            3  => 'mars',
            4  => 'avril',
            5  => 'mai',
            6  => 'juin',
            7  => 'juillet',
            8  => 'août',
            9  => 'septembre',
            10 => 'octobre',
            11 => 'novembre',
            12 => 'décembre',
        ];

        $m = $months[$month] ?? '';
        return sprintf(
            "%s %s %s à %sh%02d",
            $day,
            $m,
            $year,
            $hour,
            $min
        );
    }

    /**
     * Affiche une date relative simple : "Il y a 2 heures", "Hier à 14:05", ou format long
     */
    public static function relatif(string $raw): string
    {
        $now  = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris'));
        $date = new \DateTimeImmutable($raw, new \DateTimeZone('Europe/Paris'));

        $diff = $now->getTimestamp() - $date->getTimestamp();

        if ($diff < 0) {
            return self::formatFr($raw); // futur improbable
        }

        if ($diff < 3600) {
            $minutes = max(1, floor($diff / 60));
            return "Il y a {$minutes} " . ($minutes > 1 ? "minutes" : "minute");
        }

        if ($diff < 86400) {
            $heures = floor($diff / 3600);
            return "Il y a {$heures} " . ($heures > 1 ? "heures" : "heure");
        }

        if ($diff < 172800) {
            return "Hier à " . $date->format('H:i');
        }

        return self::formatFr($raw);
    }

    public static function formatDateSimple(string $raw): string
    {
        $ts = strtotime($raw);
        if ($ts === false) {
            return $raw;
        }

        $day   = date('d', $ts);
        $month = (int)date('n', $ts);
        $year  = date('Y', $ts);

        $months = [
            1  => 'janvier',
            2  => 'février',
            3  => 'mars',
            4  => 'avril',
            5  => 'mai',
            6  => 'juin',
            7  => 'juillet',
            8  => 'août',
            9  => 'septembre',
            10 => 'octobre',
            11 => 'novembre',
            12 => 'décembre',
        ];

        $m = $months[$month] ?? '';
        return sprintf("%s %s %s", $day, $m, $year);
    }
}
