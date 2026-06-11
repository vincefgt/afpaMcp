<?php

declare(strict_types=1);

namespace App\Builder;

use App\Entity\ApiGatewayHeaders;

class ApiGatewayOptionsBuilder
{
    /**
     * Build the options array of the call
     *
     * @param ApiGatewayHeaders $headers
     * @param array             $data
     *
     * @return array
     */
    public function buildOptions(ApiGatewayHeaders $headers, array $data): array
    {
        return [
            'http_errors' => false,
            'headers' => $headers->toArray(),
            'form_params' => $data
        ];
    }

    /**
     * @param ApiGatewayHeaders $headers
     * @param array             $data
     *
     * @return array
     */
    public function buildMultiPartOptions(ApiGatewayHeaders $headers, array $data): array
    {

        $options = [
            'http_errors' => false,
            'headers' => $headers->toArray(),
            'multipart' => []
        ];

        foreach ($data as $key => $value) {
            $field = [
                'name' => $key,
                'contents' => $value
            ];

            if (str_contains($key, 'file')) {
                if (is_array($value)) {
                    $field['filename'] = $value[0];
                    $field['contents'] = $value[1];
                }

                if (!is_array($value)) {
                    $field['filename'] = $key;
                }
            }


            $options['multipart'][] = $field;
        }
        return $options;
    }
}
