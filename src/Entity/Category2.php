<?php

namespace Lopi\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Knp\DoctrineBehaviors\Model\Translatable\TranslatableTrait;
use Lopi\Repository\Category2Repository;

/**
 * @ORM\Entity(repositoryClass=Category2Repository::class)
 */
class Category2 implements TranslatableInterface
{
    use TranslatableTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $someFieldYouDoNotNeedToTranslate;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSomeFieldYouDoNotNeedToTranslate(): ?string
    {
        return $this->someFieldYouDoNotNeedToTranslate;
    }

    public function setSomeFieldYouDoNotNeedToTranslate(string $someFieldYouDoNotNeedToTranslate): self
    {
        $this->someFieldYouDoNotNeedToTranslate = $someFieldYouDoNotNeedToTranslate;

        return $this;
    }
}
