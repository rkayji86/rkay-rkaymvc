<?php

namespace rkay\rkaymvc;

use rkay\rkaymvc\db\DbModel;

abstract class UserModel extends DbModel
{

    abstract public function getDisplayName(): string;

}