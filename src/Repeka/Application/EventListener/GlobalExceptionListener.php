<?php
namespace Repeka\Application\EventListener;

use Psr\Log\LoggerInterface;
use Repeka\Application\Entity\UserEntity;
use Repeka\Domain\Exception\DomainException;
use Repeka\Domain\Exception\NotFoundException;
use Repeka\Domain\Utils\StringUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Twig\Environment;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GlobalExceptionListener {
    use TargetPathTrait;

    const FIREWALL_NAME = 'main';
    const ADMIN_PANEL_PREFIX = '/admin/';

    private $isDebug;

    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var SessionInterface */
    private $session;
    /** @var Environment */
    private $twig;
    /** @var string */
    private $errorPageTwigTemplate;
    /** @var LoggerInterface */
    private $exceptionLogger;
    /** @var LoggerInterface */
    private $userErrorLogger;

    public function __construct(
        $isDebug,
        string $theme,
        TokenStorageInterface $tokenStorage,
        SessionInterface $session,
        LoggerInterface $exceptionLogger,
        LoggerInterface $userErrorLogger,
        Environment $twig
    ) {
        $this->isDebug = $isDebug;
        $this->tokenStorage = $tokenStorage;
        $this->session = $session;
        $this->twig = $twig;
        $this->errorPageTwigTemplate = StringUtils::joinPaths($theme, 'error-page.twig');
        $this->exceptionLogger = $exceptionLogger;
        $this->userErrorLogger = $userErrorLogger;
    }

    public function onException(GetResponseForExceptionEvent $event) {
        $exception = $event->getException();
        $request = $event->getRequest();
        if (in_array('application/json', $request->getAcceptableContentTypes())) {
            $errorResponse = $this->createErrorResponse($exception, $request);
            $event->setResponse($errorResponse);
        } else {
            $responseStatus = $this->detectResponseStatus($exception);
            try {
                $responseContent = $this->twig->render(
                    $this->errorPageTwigTemplate,
                    ['exception' => $exception, 'responseStatus' => $responseStatus]
                );
            } catch (\Exception $e) {
                $responseContent = $this->twig->render(
                    'error-page.twig',
                    ['exception' => $exception, 'responseStatus' => $responseStatus]
                );
            }
            $response = new Response($responseContent, $responseStatus);
            $event->setResponse($response);
        }
        $response = $event->getResponse() ?: new Response(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        $logger = $response->isClientError() ? $this->userErrorLogger : $this->exceptionLogger;
        $logger->error($this->getFormattedExceptionString($request, $response, $exception));
    }

    private function detectResponseStatus(\Exception $exception): int {
        $responseStatus = $exception instanceof DomainException ? $exception->getCode() : Response::HTTP_INTERNAL_SERVER_ERROR;
        $responseStatus = $exception instanceof HttpException ? $exception->getStatusCode() : $responseStatus;
        $responseStatus = $exception instanceof AccessDeniedException ? Response::HTTP_FORBIDDEN : $responseStatus;
        $responseStatus = $exception instanceof NotFoundException ? Response::HTTP_NOT_FOUND : $responseStatus;
        $responseStatus = $exception instanceof NotFoundHttpException ? Response::HTTP_NOT_FOUND : $responseStatus;
        return $responseStatus;
    }

    public function createErrorResponse(\Exception $e, Request $request) {
        if ($e instanceof AuthenticationException || $e instanceof AccessDeniedException) {
            return $this->createUnauthorizedResponse($request);
        } elseif ($e instanceof NotFoundHttpException) {
            return $this->createJsonResponse(404, $e->getMessage());
        } elseif ($e instanceof DomainException) {
            return $this->createDomainExceptionResponse($e);
        } elseif ($e instanceof \InvalidArgumentException) {
            return $this->createJsonResponse(400, $e->getMessage());
        } else {
            $message = $this->isDebug ? $e->getMessage() : 'Internal server error.';
            return $this->createJsonResponse(500, $message);
        }
    }

    private function createUnauthorizedResponse(Request $request) {
        if ($this->tokenStorage->getToken()->getUser() instanceof UserEntity) {
            return $this->createJsonResponse(403, 'Forbidden');
        } else {
            $this->saveTargetUrlIfFromAdminPanel($request);
            return $this->createJsonResponse(401, 'Unauthorized');
        }
    }

    private function createJsonResponse(int $statusCode, string $message, array $extras = []): JsonResponse {
        return new JsonResponse(
            array_merge(
                [
                    'message' => mb_convert_encoding($message, 'UTF-8', 'UTF-8'),
                ],
                $extras
            ),
            $statusCode
        );
    }

    private function createDomainExceptionResponse(DomainException $exception): JsonResponse {
        return $this->createJsonResponse(
            $exception->getCode(),
            $exception->getMessage(),
            [
                'errorMessageId' => $exception->getErrorMessageId(),
                'params' => $exception->getParams(),
            ]
        );
    }

    private function saveTargetUrlIfFromAdminPanel(Request $request) {
        $callingAddress = $request->headers->get('referer');
        if ($offset = strpos($callingAddress, self::ADMIN_PANEL_PREFIX)) {
            $path = substr($callingAddress, $offset);
            $this->saveTargetPath($this->session, self::FIREWALL_NAME, $path);
        }
    }

    private function getFormattedExceptionString(Request $request, Response $response, \Exception $exception): string {
        $exceptionLines = preg_split("/[\n\r]+/", (string)$exception);
        $outputLines = ['', 'Response status: ' . $response->getStatusCode(), 'URL: ' . $request->getMethod() . ' ' . $request->getUri()];
        while (count($exceptionLines) > 0 && strlen($exceptionLines[0]) > 0 && $exceptionLines[0][0] != '#') {
            $outputLines[] = array_shift($exceptionLines); // copy lines until stack trace
        }
        foreach ($exceptionLines as $line) {
            $outputLines[] = preg_replace('/\): +/', "):\n    ", $line, 2);
        }
        return implode("\n", $outputLines) . PHP_EOL;
    }
}
