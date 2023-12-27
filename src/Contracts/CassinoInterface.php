<?php

namespace GamesPackage\Contracts;

use Illuminate\Http\Request;

interface CassinoInterface
{
    public function play(Request $request);
}