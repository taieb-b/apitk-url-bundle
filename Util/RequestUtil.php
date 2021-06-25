<?php

declare(strict_types=1);

namespace Shopping\ApiTKUrlBundle\Util;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class RequestUtil.
 *
 * @package Shopping\ApiTKUrlBundle\Util
 */
class RequestUtil
{
    /**
     * @param RequestStack $requestStack
     *
     * @return Request
     */
    public static function getMainRequest(RequestStack $requestStack): ?Request
    {
        return method_exists($requestStack, 'getMainRequest')
            ? $requestStack->getMainRequest() // symfony >= 5.3
            : $requestStack->getMasterRequest(); // symfony <= 5.2 @phpstan-ignore-line
    }
}
