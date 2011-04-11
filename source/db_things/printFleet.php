#!/usr/bin/php
<?php
/**
 * this dumps out some information of the given fleetid
 * @var unknown_type
 */

$USE_OB = false;
if(!isset($_SERVER['DOCUMENT_ROOT']) || strlen($_SERVER['DOCUMENT_ROOT']) <= 0)
    $_SERVER['DOCUMENT_ROOT'] = getcwd()."/..";
    
require_once($_SERVER['DOCUMENT_ROOT'].'/include/config_inc.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/engine/include.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/include/util.php' );

if ( ! isset( $_SERVER['argv'][1] ) )
{
    fputs( STDERR, "Usage: " . $_SERVER['argv'][0] . " <Fleet-ID>\n" );
    exit( 1 );
}
else
{
    $databases = get_databases();
    $dbnames = array_keys( $databases );
    if ( ! define_globals( $dbnames[0] ) )
    {
        fputs( STDERR, "Unknown database.\n" );
        exit( 1 );
    }
}

$dh = opendir( global_setting( "DB_FLEETS" ) );
$fname = $_SERVER['argv'][1];
$path = global_setting( "DB_FLEETS" ) . "/" . $fname;

if ( ! is_file( $path ) )
{
    fputs( STDERR, "ERROR: " . $path . " doesnt exists!\n" );
    exit( 1 );
}
else 
{
    if ( ! is_readable( $path ) )
    {
        fputs( STDERR, "ERROR: " . $path . " cant be read, check permissions!\n" );
        exit( 1 );
    }
}
   
$fleet = Classes::Fleet( urldecode( $fname ) );

if ( $fleet->getStatus() != 1 )
{
    fputs( STDERR, "ERROR: invalid status of fleet: " . $fleet->getName() . "!\n" );
}

// get all related users to this fleet and print out!
$relatedUsers = $fleet->getUsersList();
$i = 0;

foreach ( $relatedUsers as $uname )
{
    if ( $i == 0 )
    {
        echo "users: ";
        echo $uname;
    }
    else
    {
        echo ", " . $uname;
    }
}

echo "\n";

// get all targets of this fleet and print them out
$targetInfo = $fleet->getTargetsInformation();

foreach ( $targetInfo as $targetCoord => $targetArray )
{
    echo "fleet flying to: " . $targetCoord;
    if ( $targetArray[1] )
    {
        echo " [flying back] ";
    }
    echo " [" . strtolower( fleetType2String( $targetArray[0] ) ) . "] ";
    echo "\n";
}

// get fleet content, which ships are flying in it, ressources etc.
$fleetContent = $fleet->getFleetContent();

// grab all users which have ships in that fleet and list them
foreach ( $fleetContent as $uName => $userFleet )
{
    echo "  listing ships of user: " . $uName . "\n";
    
    // get all ships in that array
    $ships = $userFleet[0];
    
    foreach ( $ships as $shipID => $shipCount )
    {
        echo "    " . $shipCount . " x " . fleetID2String( $shipID ) . "\n";
    }
    
    echo "    coming from: " . $userFleet[1] . "\n";
    echo "    speed: " . $userFleet[2] * 100 . "%\n";
    $res = $userFleet[3][0];
    $trit = $userFleet[3][2];
    echo "    transport ressources: carbon: " . $res[0] . " alu: " . $res[1] . " wolf: " . $res[2] . " rad: " . $res[3] . " trit: " . $res[4] . "\n";
    echo "    remaining tritium: " . $trit . "\n";
    $trade = $userFleet[4][0];
    echo "    trading ressources: carbon: " . $trade[0] . " alu: " . $trade[1] . " wolf: " . $trade[2] . " rad: " . $trade[3] . " trit: " . $trade[4] . "\n";
    echo "    fleet started at: " . date( "r", $userFleet['startzeit'] ) . "\n";
}

if($fleet->getArrivalTime() == "" || $fleet->getArrivalTime() <= 0)
	echo "arrival time: ERROR\n";
else
	echo "arrival time: '".date("r",$fleet->getArrivalTime())."'\n";

closedir( $dh );
?>
