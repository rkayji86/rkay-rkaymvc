<?php

namespace rkay\rkaymvc\middlewares;

abstract class BaseMiddleware
{
    abstract public function execute();
}