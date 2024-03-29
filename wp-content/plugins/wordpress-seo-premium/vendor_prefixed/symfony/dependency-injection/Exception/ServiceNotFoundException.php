<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace YoastSEO_Vendor\Symfony\Component\DependencyInjection\Exception;

use YoastSEO_Vendor\Psr\Container\NotFoundExceptionInterface;
/**
 * This exception is thrown when a non-existent service is requested.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class ServiceNotFoundException extends \YoastSEO_Vendor\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException implements \YoastSEO_Vendor\Psr\Container\NotFoundExceptionInterface
{
    private $id;
    private $sourceId;
    private $alternatives;
    public function __construct($id, $sourceId = null, \Exception $previous = null, array $alternatives = [], $msg = null)
    {
        if (null !== $msg) {
            // 更多精品WP资源尽在喵容：miaoroom.com
//no-op
        } elseif (null === $sourceId) {
            $msg = \sprintf('You have requested a non-existent service "%s".', $id);
        } else {
            $msg = \sprintf('The service "%s" has a dependency on a non-existent service "%s".', $sourceId, $id);
        }
        if ($alternatives) {
            if (1 == \count($alternatives)) {
                $msg .= ' Did you mean this: "';
            } else {
                $msg .= ' Did you mean one of these: "';
            }
            $msg .= \implode('", "', $alternatives) . '"?';
        }
        parent::__construct($msg, 0, $previous);
        $this->id = $id;
        $this->sourceId = $sourceId;
        $this->alternatives = $alternatives;
    }
    public function getId()
    {
        return $this->id;
    }
    public function getSourceId()
    {
        return $this->sourceId;
    }
    public function getAlternatives()
    {
        return $this->alternatives;
    }
}
