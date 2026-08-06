<?php
namespace Ahy\Automation\Model;

class TimeResolver
{
    const VISIBILITY_HIDE = 1;
    const VISIBILITY_SHOW = 4;

    /**
     * Determine desired visibility based on EST time
     *
     * @param string|null $simulateDate optional simulate datetime string
     * @return int
     */
    public function getDesiredVisibility($simulateDate = null)
    {
        $tz = new \DateTimeZone('America/New_York');
        $now = $simulateDate ? new \DateTime($simulateDate, $tz) : new \DateTime('now', $tz);

        $dayOfWeek = (int)$now->format('N'); // 1=Mon,7=Sun
        $hourMinute = (int)$now->format('Hi'); // e.g., 0730 -> 730

        // Weekdays 07:00–17:00 → hide
        if ($dayOfWeek >= 1 && $dayOfWeek <= 5 && $hourMinute >= 700 && $hourMinute < 1700) {
            return self::VISIBILITY_HIDE;
        }

        // All other times → show
        return self::VISIBILITY_SHOW;
    }
}
