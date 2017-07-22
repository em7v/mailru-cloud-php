<?php
/**
 * Created by PhpStorm.
 * User: friday
 * Date: 20.07.2017
 * Time: 23:51
 */

namespace Friday14\CloudMailRu\Exceptions;

use Psr\Http\Message\ResponseInterface;


class BadRequest extends \Exception
{
    public function __construct(ResponseInterface $response)
    {

    }
}