<?php

namespace App\Helpers;

use App\Models\User;

class HNHelper
{
    /**
     * Generate a new HN based on the current Thai year and running number.
     * Format: HN+YY+XXXX (YY = 2-digit Thai year, XXXX = running number for that year)
     * e.g. HN690018
     *
     * @return string
     */
    public static function generate()
    {
        $thaiYear = (int) date('Y') + 543;
        $yy = substr((string) $thaiYear, -2); // e.g. "69" for 2569
        $prefix = 'HN' . $yy;

        // Count only users that have a patient_id starting with the current prefix 'HN69'
        $countThisYear = User::where('patient_id', 'like', $prefix . '%')->count();

        $nextSequence = $countThisYear + 1;

        // Return format like: HN690018
        return $prefix . str_pad($nextSequence, 4, '0', STR_PAD_LEFT);
    }
}
