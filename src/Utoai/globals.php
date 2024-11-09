<?php

// if (! function_exists('asset')) {
//     function asset(string $asset)
//     {
//         return Utoai\asset($asset);
//     }
// }


if (! function_exists('view')) {
    function view()
    {
        return Utoai\view(...func_get_args());
    }
}
