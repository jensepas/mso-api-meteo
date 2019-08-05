<?php

namespace api;

class stuff
{
    public $return = ["error" => "0", "message" => ""];

    public function todo_get($params = []): array
    {
        $this->return['message'] = "I just GET it.";

        return $this->return;
    }

    public function todo_post($params = []): array
    {
        $this->return['message'] = "I just POST it.";

        return $this->return;
    }

    public function todo_put($params = []): array
    {
        $this->return['message'] = "I just PUT it.";

        return $this->return;
    }

    public function todo_delete($params = []): array
    {
        $this->return['message'] = "I just DELETE it.";

        return $this->return;
    }
}