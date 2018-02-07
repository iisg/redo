<?php
namespace Repeka\Domain\Cqrs;

/**
 * Every CQRS command that does not provide a validator should implement this interface in order to indicate this fact.
 * @SuppressWarnings("PHPMD.NumberOfChildren")
 */
interface NonValidatedCommand extends Command {
}
