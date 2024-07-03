<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;

class GoogleBookApiService
{
    protected const GOOGLE_BOOKS_API_BASE_URL = 'https://www.googleapis.com/books/v1/volumes';

    /**
     * Gets data from Google Api
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function getData(array $params): array
    {

        $data = Http::get(self::GOOGLE_BOOKS_API_BASE_URL . $this->getSearchParams($params) . '&key=' . env('GOOGLE_KEY'));

        if ($data->ok()) {
            return $data->json();
        } else {
            throw new \Exception('Не получается установить соединение');
        }
    }

    /**
     * Builds search query with the given params
     * @param array $params
     * @return string
     */

    private function getSearchParams(array $params): string
    {
        $query = '?q=';

        foreach ($params as $key => $value) {
            $query = $query . $key . ':' . $value . '+';
        }

        // trims the last '+'
        $query = rtrim($query, '+');

        return $query;
    }
}
