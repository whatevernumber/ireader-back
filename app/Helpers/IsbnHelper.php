<?php

namespace App\Helpers;

/**
 * Checks if the provided isbn is correct
 */
class IsbnHelper
{
    // dividers for isbn-10 and isbn-13
    private const DIVIDER_10 = 11;
    private const DIVIDER_13 = 10;

    /**
     * Checks if isbn is valid
     * @param string $isbn
     * @return bool
     */
    public function checkIsbn(string $isbn): bool
    {
        $resultArray = [];

        // gets the numbers (the last character can be possible x)
        preg_match_all('/\d|X/', $isbn, $resultArray);
        $resultArray = $resultArray[0];

        $count = count($resultArray);

        // removes the last digit
        $last_index = array_pop($resultArray);

        $last_index = $last_index === 'X' ? 'X' : intval($last_index);

        // the divider is different to ISBN-10 and ISBN-13
        $divider = $count === 10 ? self::DIVIDER_10 : self::DIVIDER_13;

        // gets the sum for the isbn digits
        $sum = $this->getIsbnSum($resultArray, $count);

        // finds the modulo
        $mod = $sum % $divider;

        // subtracts the remainder
        $result = $divider - $mod;

        // if the result is 11 and the digits are from the ISBN-10 - the result turns to 0
        if ($result === 11 && $divider === self::DIVIDER_10) {
            $result = 0;
        } else if ($result === 10) {
            // depends on the format of ISBN 10 turns to 'X' or 0
            $divider === self::DIVIDER_10 ? $result = 'X' : $result = 0;
        }

        // check if the last digit is valid
        return $last_index === $result;
    }

    /**
     * Calculates the sum for ISBN-10 or ISBN-13 numbers
     * @param array $numbers - array of numbers
     * @param int $count - number of digits
     * @return int - sum of the numbers
     */
    private function getIsbnSum(array $numbers, int $count): int
    {
        $sum = 0;

        // for isbn-10
        if ($count === 10) {

            $reversed = array_reverse($numbers);

            for ($i = 0; $i < $count - 1; $i++) {
                $sum += $reversed[$i] * ($i + 2);
            }

        } else {
            // for isbn-13
            foreach ($numbers as $key => $value) {
                if ($key % 2) {
                    // if the index of the number is odd
                    $sum += 3 * $value;
                } else {
                    // if the index of the number is even (or 0)
                    $sum += $value;
                }
            }
        }

        return $sum;
    }
}
