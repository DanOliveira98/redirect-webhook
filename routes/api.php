<?php

Route::post("games-package", [\GamesPackage\Controllers\CreateGameController::class, 'createGame']);