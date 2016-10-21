<?php
declare (strict_types = 1);
namespace Repeka\CoreModule\Infrastructure\Application;

use Repeka\CoreModule\Application\Command\Command;
use Repeka\CoreModule\Application\CommandBus;
use SimpleBus\Message\Bus\MessageBus;

final class SimpleBusCommandBusAdapter implements CommandBus {
    /**
     * @var MessageBus
     */
    private $messageBus;

    /**
     * SimpleBusCommandBusAdapter constructor.
     * @param MessageBus $messageBus
     */
    public function __construct(MessageBus $messageBus) {
        $this->messageBus = $messageBus;
    }

    public function handle(Command $command) {
        $this->messageBus->handle($command);
    }
}
