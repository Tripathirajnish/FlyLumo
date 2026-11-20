<?php

/*
|--------------------------------------------------------------------------
| FaresHunt fare-rule switches
|--------------------------------------------------------------------------
| The code has the SAME defaults built in, so nothing breaks if this file is
| missing or the config cache is old. Edit a value here only if you want to
| change behaviour (then the config cache must be refreshed by your server
| admin, otherwise the built-in defaults below keep applying).
*/

return [

    // true  = most specific rule wins (airport > country, airline-specific > all-airlines; tie -> lowest fare)
    // false = old behaviour: the lowest resulting adult fare wins
    'most_specific_wins' => true,

    // true  = a discount never pushes adult/child fare below the rule's "Minimum fare"
    // false = old behaviour: Minimum fare is ignored
    'enforce_min_fare' => true,

    // true  = FareLeader one-way / Premium / First searches follow the rule's tick boxes
    // false = old behaviour: those tick boxes are ignored on FareLeader
    'meta_strict_flags' => true,

    // Zero-fare guard: a rule that would make the adult fare lower than this (USD) is skipped.
    'min_sell_price' => 1,

];
