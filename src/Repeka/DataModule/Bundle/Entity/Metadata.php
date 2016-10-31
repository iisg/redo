<?php
namespace Repeka\DataModule\Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Repeka\DataModule\Bundle\Validator\Constraints\RequiredInMainLanguage;
use Repeka\DataModule\Bundle\Validator\Constraints\ValidMetadataControl;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="`metadata`")
 * @ORM\Entity
 */
class Metadata {
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=25)
     * @Assert\NotBlank()
     * @ValidMetadataControl()
     */
    private $control;

    /**
     * @ORM\Column(type="json_document", options={"jsonb": true})
     * @RequiredInMainLanguage
     */
    private $label;

    /**
     * @ORM\Column(type="json_document", options={"jsonb": true})
     */
    private $description;

    /**
     * @ORM\Column(type="json_document", options={"jsonb": true})
     */
    private $placeholder;

    public function getId() {
        return $this->id;
    }

    public function getControl() {
        return $this->control;
    }

    public function setControl($control): Metadata {
        $this->control = $control;
        return $this;
    }

    public function getLabel() {
        return $this->label;
    }

    public function setLabel($label): Metadata {
        $this->label = $label;
        return $this;
    }

    public function getDescription() {
        return $this->description;
    }

    public function setDescription($description): Metadata {
        $this->description = $description;
        return $this;
    }

    public function getPlaceholder() {
        return $this->placeholder;
    }

    public function setPlaceholder($placeholder): Metadata {
        $this->placeholder = $placeholder;
        return $this;
    }
}
