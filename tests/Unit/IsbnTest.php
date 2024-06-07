<?php

namespace Tests\Unit;

use App\Helpers\IsbnHelper;

use PHPUnit\Framework\TestCase;

class IsbnTest extends TestCase
{
    /**
     * Correct ISBN13 is valid
     */
    public function test_isbn13_is_correct(): void
    {
        $helper = new IsbnHelper();
        $result = $helper->checkIsbn(fake()->isbn13());

        self::assertTrue($result);
    }

    /**
     * Correct ISBN10 is valid
     */
    public function test_isbn10_is_correct(): void
    {
        $helper = new IsbnHelper();
        $result = $helper->checkIsbn(fake()->isbn10());

        self::assertTrue($result);
    }

    /**
     * Incorrect ISBN is not valid
     */
    public function test_random_number_is_not_valid_isbn(): void
    {
        $helper = new IsbnHelper();
        $result = $helper->checkIsbn('1234567890');

        $this->assertFalse($result);
        $result = $helper->checkIsbn('1234567890123');

        $this->assertFalse($result);
    }
}
