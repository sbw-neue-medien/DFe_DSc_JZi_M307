<?php declare(strict_types=1);

/**
 * function to check GET/POST parameter
 */
function check_parameter( array $param, array $required): bool
{
  $match = array_intersect_key($required, array_keys($param));
  return count($match) === count($required);
}