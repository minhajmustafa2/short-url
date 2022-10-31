<?php

Route::get('/s/{shortURLKey}', 'AshAllenDesign\ShortURL\Controllers\ShortURLController')->name('short-url.invoke');
