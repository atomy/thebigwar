<?php

class Battle
{

    function battle( $angreifer, $verteidiger )
    {
        echo ( microtime() . "  Function Battle S T A R T.\n" );
        $i = 0;
        while ( $i < 1000 ) {
            $rand = rand( 1, 10000000000 );
            #$array[] = $i*$rand;
            #unset($array);
            $i ++;
        }
        echo ( microtime() . "  Function Battle E N D E.\n" );
        return true;
        #return array(true, $angreifer, $verteidiger, false, false, false);
    }
}
?>