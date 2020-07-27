<?php

namespace Core\View;

use League\Plates\Engine;

class View
{

  /**
   * @var Engine
   */
    private $engine;

    public function __construct(string $path = CONFIG_VIEW_PATH, string $ext = CONFIG_VIEW_EXT)
    {
        $this->engine = Engine::create($path, $ext);
    }

    /**
     * Get the value of engine
     *
     * @return  Engine
     */
    public function engine(): Engine
    {
        return $this->engine;
    }

    public function addPath(string $name, string $path): View
    {
        $this->engine->addFolder($name, $path);
        return $this;
    }

    public function render(string $template, array $data = []): string
    {
        return $this->engine->render($template, $data);
    }
}
