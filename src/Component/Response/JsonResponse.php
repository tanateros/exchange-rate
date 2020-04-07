<?php

namespace ExchangeRate\Component\Response;

class JsonResponse
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function __toString()
    {
        return json_encode($this->data);
    }
}
