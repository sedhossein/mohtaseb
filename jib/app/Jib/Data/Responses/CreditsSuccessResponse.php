<?php

namespace App\Jib\Data\Responses;

use JsonSerializable;

class CreditsSuccessResponse implements JsonSerializable
{
    private $referenceID;

    public function __construct(string $referenceID)
    {
        $this->referenceID = $referenceID;
    }

    public function jsonSerialize()
    {
        return [
            'success' => true,
            'messages' => "",
            'data' => [
                'reference_id' => $this->referenceID
            ],
        ];
    }
}
