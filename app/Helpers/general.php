<?php


function isListTrashed(): bool
{
    return auth('admin')->check() ? true : false;
}