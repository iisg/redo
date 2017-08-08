<?php
namespace Repeka\Domain\Cqrs;

/**
 * Every CQRS command that does not provide a validator should extend this class in order to indicate this fact.
 * @SuppressWarnings("PHPMD.NumberOfChildren")
 */
abstract class NonValidatedCommand extends Command {
}
