<?php

namespace App\Http\Controllers\Cabinet\Dashboard;

class DashboardManager
{
    protected array $components = [];

    public function addComponent(DashboardComponentInterface $component)
    {
        $this->components[] = $component;
        return $this;
    }

    /**
     * Отрисовывает все компоненты по очереди
     */
    public function render($user, $site = null)
    {
        $output = '';
        foreach ($this->components as $component) {
            $data = $component->getData($user, $site);
            // Отрисовываем фрагмент из папки widgets/
            $output .= view('cabinet.dashboard.widgets.' . $component->getTemplate(), $data)->render();
        }
        return $output;
    }
    /*
    public function build($user, $site = null): array
    {
        $data = [];
        foreach ($this->components as $component) {
            $data = array_merge($data, $component->getData($user, $site));
        }
        return $data;
    }
    //*/
}
