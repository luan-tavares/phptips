<?php

namespace Core\Message;

use Error;
use Core\Session\Session;

class Message
{
    /**
     * @var mixed array|string
     */
    private $text;

    private $type;

    private static $types = [
        "success",
        "error",
        "warning",
        "action",
        "info"
    ];

    public function __call($method, $params)
    {
        if (in_array($method, self::$types)) {
            if (count($params) !== 1) {
                throw new Error("Only one param in ".__CLASS__ ."::{$method}", 1);
            }
            $this->type = $method;
            $this->text = (!is_array($params[0]))?([$params[0]]):($params[0]);
            return $this;
        }
        throw new Error("Call to undefined method ".__CLASS__ ."::{$method}", 1);
    }

    public function __toString()
    {
        return $this->render();
    }

    public function text()
    {
        return $this->text;
    }

    public function getType()
    {
        return $this->type;
    }

    public function json(): string
    {
        return json_encode([$this->type => $this->text]);
    }

    public function flash(): void
    {
        Session::instance()->set("flash", $this);
    }

    public function render(): string
    {
        $render = "<ul class=\"". CONFIG_MESSAGE_MAINCLASS ." {$this->type}\">";
        foreach ($this->text as $text) {
            $render.= "<li>{$this->filter($text)}</li>";
        }
        $render .= "</ul>";
        return $render;
    }

    private function filter(string $message): string
    {
        return filter_var($message, FILTER_SANITIZE_SPECIAL_CHARS);
    }
}
