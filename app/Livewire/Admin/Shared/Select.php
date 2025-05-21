<?php

namespace App\Livewire\Admin\Shared;

use Livewire\Component;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Modelable;

class Select extends Component
{
    public $items;
    #[Locked]
    public $itemsArray;
    #[Modelable]
    public $selected = null;
    public $placeholder = 'Select option';
    public $label = "Choose item";
    public $open = false;
    public $selectedLabel = '';

    public function mount()
    {
        $this->itemsArray = $this->items->keyBy('id')->toArray();
        $this->selectedLabel = $this->selected ? $this->itemsArray[$this->selected]['name'] : $this->placeholder;
    }

    public function toggle()
    {
        $this->open = !$this->open;
    }

    public function close()
    {
        $this->open = false;
    }

    public function open()
    {
        $this->open = true;
    }

    public function select($id = null)
    {
        $this->selected = $id;
        $this->selectedLabel = $id ? $this->itemsArray[$this->selected]['name'] : $this->placeholder;
        $this->open = false;
    }

    public function render()
    {
        return view('livewire.admin.shared.select');
    }
}
