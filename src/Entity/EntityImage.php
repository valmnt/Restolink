<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


abstract class EntityImage
{

    /**
     * @ORM\Column(type="string")
     */
    protected $image;

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }
}