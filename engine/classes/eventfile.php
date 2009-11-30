<?php
	class EventFile
	{
		protected $connection=false;
		protected $status=false;
		protected $fo=false;

		function __construct()
		{
			if(!$this->status)
			{
		                $filename = s_root.'/logs/events.log';
                        $this->fo = fopen($filename, "a");

                # Datenbankverbindung herstellen
                $this->connection = sqlite_open(global_setting("EVENT_FILE"), 0666);
                if($this->connection)
                {
                    $table_check = sqlite_query($this->connection, "SELECT name FROM sqlite_master WHERE type='table' AND name='events'");
                    if(sqlite_num_rows($table_check)!=0 || sqlite_query($this->connection, "CREATE TABLE events ( time INT(11), fleet VARCHAR(16) );"))
                        $this->status = true;
                }
            }
        }

        function __destruct()
        {
            if($this->status)
            {
                # Datenbankerbindung schliessen
                sqlite_close($this->connection);
                $this->status = false;
            }
            fclose( $this->fo );
        }

        function getLastErrorMessage()
        {
            $number = sqlite_last_error($this->connection);
            if($number === false) return false;
            return $number.': '.sqlite_error_string($this->connection);
        }

        function addNewFleet($time, $id)
        {
            if(!$this->status) return false;

            $time = round($time);

                       #fwrite($this->fo, time(). "EventFile::addNewFleet(): INSERT INTO events (time, fleet) VALUES (".$time.", ".$id.");\n" );

            return sqlite_query($this->connection, "INSERT INTO events (time, fleet) VALUES ('".sqlite_escape_string($time)."', '".sqlite_escape_string($id)."');");
        }

        function removeFleet($id)
        {
            if(!$this->status) return false;
            
                        #fwrite($this->fo, time(). "EventFile::removeFleet(): DELETE FROM events WHERE fleet = ".$id."\n");

            # Aus DB loeschen
            return sqlite_query($this->connection, "DELETE FROM events WHERE fleet = '".sqlite_escape_string($id)."';");
        }

        function removeNextFleet()
        {
            if(!$this->status) return false;

            # Naechstes Feld aus der Datenbank lesen
            $query = sqlite_query($this->connection, "SELECT * FROM events WHERE time < ".time()." ORDER BY time ASC LIMIT 1;");
            $field = sqlite_fetch_array($query, SQLITE_ASSOC);

            //echo "removeNextFleet() ---- SELECT * FROM events WHERE time < ".time()." ORDER BY time ASC LIMIT 1;\n";
            #fwrite($this->fo, time(). "EventFile::removeNextFleet(): SELECT * FROM events WHERE time < ".time()." ORDER BY time ASC LIMIT 1;\n" );

            if(!$field) return false;

            #fwrite($this->fo, time(). "EventFile::removeNextFleet(): DELETE FROM events WHERE time = ".$field['time']." AND fleet = ".sqlite_escape_string($field['fleet']).";\n" );

            # Gefundenes Feld aus der Datenbank loeschen
            if(!sqlite_query($this->connection, "DELETE FROM events WHERE time = '".$field['time']."' AND fleet = '".sqlite_escape_string($field['fleet'])."';", SQLITE_ASSOC))
            {    
                //echo "removeNextFleet() ---- DELETE FROM events WHERE time = '".$field['time']."' AND fleet = '".sqlite_escape_string($field['fleet'])."';\n";
                #fwrite( $this->fo, time(). "EventFile::removeNextFleet(): DELETE FROM events WHERE time... FAILED\n" );
                return false;
            }
            return $field;
        }

        function removeCanceledFleet($fleet, $time=false)
        {
            if(!$this->status) return false;

            #fwrite($this->fo, time(). "EventFile::removeCanceledFleet(): DELETE FROM events WHERE fleet = ".$fleet."\n" );

            return sqlite_query($this->connection, "DELETE FROM events WHERE fleet = '".sqlite_escape_string($fleet)."';");
        }

        function getName()
        { # For instances
            return "eventfile";
        }

        function getStatus()
        {
            return $this->status;
        }

        function _empty()
        {
            if(!$this->status) return false;

            return sqlite_query($this->connection, "DELETE FROM events;");
        }
        
        function _getListOfEvents()
        {
            if( !$this->connection ) 
                return false;

            // return result of a query containing all events
            $results = array();    
            $results = sqlite_query($this->connection, "SELECT * FROM events;");
            
            // fehler, print error and exit
            if ( !$results )
                die( sqlite_error_string( sqlite_last_error( $this->connection ) ) );

            return $results;
        }
    }
?>
