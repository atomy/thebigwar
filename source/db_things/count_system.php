#!/usr/bin/php

<?php
$USE_OB = false;
require_once '../include/config_inc.php';
require TBW_ROOT . '/engine/include.php';

if ( ! isset( $_SERVER['argv'][1] ) )
{
    fputs( STDERR, "Usage: " . $_SERVER['argv'][0] . " <Database ID>\n" );
    exit( 1 );
}
else
{
    $databases = get_databases();
    if ( ! define_globals( $_SERVER['argv'][1] ) )
    {
        fputs( STDERR, "Unknown database.\n" );
        exit( 1 );
    }
}

$dh = opendir( global_setting( "DB_PLAYERS" ) );

$besiedelt = 0;
$activ = 0;
$anzahl = 0;
$anzahl1 = 0;

while ( ( $filename = readdir( $dh ) ) !== false )
{
    if ( ! is_file( global_setting( "DB_PLAYERS" ) . '/' . $filename ) )
        continue;
    
    $user = Classes::User( urldecode( $filename ) );
    $planets = $user->getPlanetsList();
    
    foreach ( $planets as $planet )
    {
        $besiedelt += count( $planet );
    }
    
    $last_activity = $user->getLastActivity();
    if ( $last_activity > ( time() - 259200 ) )
        $activ += 1;

}

echo "Besiedelte Planeten: ", $besiedelt, "\n\n";
echo "Aktive User innerhalb der letzten 3 Tage: ", $activ, "\n\n";

$galaxy_n = 1;
$system_n = 1;

__autoload( 'Galaxy' );
$galaxy_count = getGalaxiesCount();

$galaxy = Classes::Galaxy( $galaxy_n );

$next_system = $system_n + 1;
if ( $next_system > 999 )
    $next_system = 1;

for ( $c = 1; $c <= 999; $c ++ )
{
    $planets_count = $galaxy->getPlanetsCount( $c );
    {
        for ( $i = 1; $i <= $planets_count; $i ++ )
        {}
        $anzahl += $planets_count;
    }

}
echo "Es sind ", $anzahl, " Planetenpositionen in Galaxie 1 verfuegbar\n\n";

#$prozent = $besiedelt * 100 / $anzahl;


#echo "Es sind ", round($prozent, 2), " Prozent belegt!\n"; 


$galaxy_n1 = 2;
$system_n = 1;

__autoload( 'Galaxy' );
$galaxy_count = getGalaxiesCount();

$galaxy1 = Classes::Galaxy( $galaxy_n1 );

$next_system = $system_n + 1;
if ( $next_system > 999 )
    $next_system = 1;

for ( $c = 1; $c <= 999; $c ++ )
{
    $planets_count = $galaxy1->getPlanetsCount( $c );
    {
        for ( $i = 1; $i <= $planets_count; $i ++ )
        {}
        $anzahl1 += $planets_count;
    }

}
$gesamtpp = $anzahl + $anzahl1;
echo "Es sind ", $anzahl1, " Planetenpositionen in Galaxie 2 verfuegbar\n\n";
echo "Es sind ", $gesamtpp, " Planetenpositionen gesamt verfuegbar\n\n";

$prozent = $besiedelt * 100 / ( $gesamtpp );

echo "Es sind ", round( $prozent, 2 ), " Prozent belegt!\n";
closedir( $dh );
?>

