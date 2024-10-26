<?php

namespace Utoai\Monk\Assets\Contracts;

interface Bundle
{
    public function css();

    public function js();

    public function runtime();
}
