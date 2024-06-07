<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;

class GoogleBookApiHelper
{
    protected const GOOGLE_VOLUMES_URL = 'https://www.googleapis.com/books/v1/volumes';

    /**
     * Gets data from Google Api
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function getData(array $params): array
    {

        $data = Http::get(self::GOOGLE_VOLUMES_URL . $this->getQuery($params));

        if ($data->ok()) {
            return $data->json();
        } else {
            throw new \Exception('Не получается установить соединение');
        }
    }

    /**
     * Builds query with the given params
     * @param array $params
     * @return string
     */
    public function getQuery(array $params): string
    {
        $query = '?q=';

        foreach ($params as $key => $value) {
            $query = $query . $key . '=' . $value;
        }

        return $query . '&key=' . env('GOOGLE_KEY');
    }
}
