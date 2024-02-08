<?php

namespace App\Lib;

class TimeToHumanReadable
{
    /**
     * @param \DateTime $time
     *
     * @return string
     */
    public static function generate(\DateTimeInterface $time): string
    {
        $now = time();
        $last = $time->getTimestamp();

        $diff = $now - $last;

        $human = $time->format('d.m.Y H:i:s');

        if ($diff < 11) {
            $human = 'for a few seconds';
        } elseif ($diff < 60) {
            $human = sprintf('for %d second%s', $diff, $diff > 1 ? 's' : '');
        } elseif ($diff < 3600) {
            $min = round($diff / 60);
            $human = sprintf('for %d minute%s', $min, $min > 1 ? 's' : '');
        } elseif ($diff < 216000) {
            $hour = round($diff / 60 / 60);
            $human = sprintf('for %d hour%s', $hour, $hour > 1 ? 's' : '');
        }

        return $human;
    }
}
