<?php

namespace Lopi\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractPersonalTranslation;
use Lopi\Repository\CategoryTranslationRepository;

/**
 * @ORM\Entity(repositoryClass=CategoryTranslationRepository::class)
 * @ORM\Table(name="category_translations",
 *      uniqueConstraints={@ORM\UniqueConstraint(name="lookup_unique_idx", columns={
 *          "locale", "object_id", "field"
 *      })}
 * )
 */
class CategoryTranslation extends AbstractPersonalTranslation
{

    /**
     * @ORM\ManyToOne(targetEntity=Category::class, inversedBy="translations")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $object;

    public function __construct(string $locale, $field, $value)
    {
        $this->setLocale($locale);
        $this->setField($field);
        $this->setContent($value);
    }
}
