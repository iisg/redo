<?php
namespace Repeka\Website\Controller;

use Repeka\Domain\Entity\ResourceEntity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class WebsiteResourcesController extends Controller {
    /** @var NormalizerInterface */
    private $normalizer;

    public function __construct(NormalizerInterface $normalizer) {
        $this->normalizer = $normalizer;
    }

    /**
     * @Route("/resources/{resource}")
     * @Template
     */
    public function resourceViewAction(ResourceEntity $resource) {
        return ['resource' => $this->normalizer->normalize($resource)];
    }
}
