<?php

declare(strict_types=1);

namespace App\Handler;

use Symfony\Component\HttpFoundation\Request;

class RequestKeysHandler
{
    /** Name of the id key in the object
     *
     * @var string
     */
    const PRIMARY_KEY_NAME_FIELD = 'primaryKeyName';

    /** Name of the object containing the id key
     *
     * @var string
     */
    const DATA_KEY_NAME_FIELD = 'dataKeyName';

    /**
     * Check if the primary key field matches the primary key in the object
     *
     * @param Request $request
     *
     * @return bool
     */
    public function checkPrimaryKey(Request $request): bool
    {
        $primaryKeyName = $request->attributes->get(self::PRIMARY_KEY_NAME_FIELD);
        $dataKeyName = $request->attributes->get(self::DATA_KEY_NAME_FIELD);
        $data = $request->request->all();

        if (!empty($data[$dataKeyName])) {
            $data = json_decode($data[$dataKeyName], true);
            if ($data[$primaryKeyName] >= 0) {
                return true;
            }
        }

        return false;
    }
}
