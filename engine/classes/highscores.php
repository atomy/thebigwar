<?php
	class Highscores
	{
		protected $connection=false;
		protected $status=false;

		function __construct()
		{
			if(!$this->status)
			{
				# Datenbankverbindung herstellen
				$this->connection = sqlite_open(global_setting("DB_HIGHSCORES"), 0666);
                if($this->connection)
                {
                    $table_check1 = sqlite_query($this->connection, "SELECT name FROM sqlite_master WHERE type='table' AND name='highscores_users'");
                    $table_check2 = sqlite_query($this->connection, "SELECT name FROM sqlite_master WHERE type='table' AND name='highscores_alliances'");
                    if((sqlite_num_rows($table_check1)>0 || sqlite_query($this->connection, "CREATE TABLE highscores_users ( username, alliance, scores INTEGER, changed INTEGER );")) && (sqlite_num_rows($table_check2)>0 || sqlite_query($this->connection, "CREATE TABLE highscores_alliances ( tag, scores_average INTEGER, scores_total INTEGER, members_count INTEGER, changed INTEGER );")))
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
        }

        function getName()
        { # For instances
            return "highscores";
        }

        function getStatus()
        {
            return $this->status;
        }

        function updateUser($username, $alliance=false, $scores=false)
        {
            if(!$this->status) return false;

            $exists_query = sqlite_query($this->connection, "SELECT username FROM highscores_users WHERE username='".sqlite_escape_string($username)."' LIMIT 1;");
            $exists = (sqlite_num_rows($exists_query) > 0);

            if($scores !== false) $scores = (float) $scores;

            if($exists)
            {
                if($alliance === false && $scores === false) return true;

                $query = "UPDATE highscores_users SET ";
                $set = array();
                if($alliance !== false) $set[] = "alliance = '".sqlite_escape_string($alliance)."'";
                if($scores !== false)
                {
                    $set[] = "scores = '".sqlite_escape_string($scores)."'";
                    $set[] = "changed = '".sqlite_escape_string(microtime(true))."'";
                }
                $query .= implode(', ', $set);
                $query .= " WHERE username = '".sqlite_escape_string($username)."';";
            }
            else
            {
                $scores = (float) $scores;
                $query = "INSERT INTO highscores_users ( username, alliance, scores, changed ) VALUES ( '".sqlite_escape_string($username)."', '".sqlite_escape_string($alliance)."', '".sqlite_escape_string($scores)."', '".sqlite_escape_string(microtime(true))."' );";
            }

            return sqlite_query($this->connection, $query);
        }

        function renameUser($old_username, $new_username)
        {
            if(!$this->status) return false;

            return sqlite_query($this->connection, "UPDATE highscores_users SET username = '".sqlite_escape_string($new_username)."' WHERE username = '".sqlite_escape_string($old_username)."';");
        }

        function renameAlliance($old_alliance, $new_alliance)
        {
            if(!$this->status) return false;

            return sqlite_query($this->connection, "UPDATE highscores_alliances SET tag = '".sqlite_escape_string($new_alliance)."' WHERE tag = '".sqlite_escape_string($old_alliance)."';");
        }

        function updateAlliance($tag, $scores_average=false, $scores_total=false, $members_count=false)
        {
            if(!$this->status) return false;

            $exists_query = sqlite_query($this->connection, "SELECT tag FROM highscores_alliances WHERE tag='".sqlite_escape_string($tag)."' LIMIT 1;");
            $exists = (sqlite_num_rows($exists_query) > 0);

            if($exists)
            {
                if($scores_average === false && $scores_total === false && $members_count === false) return true;

                $query = "UPDATE highscores_alliances SET ";
                $set = array();
                if($scores_average !== false) $set[] = "scores_average = '".sqlite_escape_string($scores_average)."'";
                if($scores_total !== false) $set[] = "scores_total = '".sqlite_escape_string($scores_total)."'";
                if($members_count !== false) $set[] = "members_count = '".sqlite_escape_string($members_count)."'";
                $query .= implode(', ', $set);
                $query .= " WHERE tag = '".sqlite_escape_string($tag)."';";
            }
            else $query = "INSERT INTO highscores_alliances ( tag, scores_average, scores_total, members_count ) VALUES ( '".sqlite_escape_string($tag)."', '".sqlite_escape_string($scores_average)."', '".sqlite_escape_string($scores_total)."', '".sqlite_escape_string($members_count)."' );";

            return sqlite_query($this->connection, $query);
        }

        function removeEntry($type, $id)
        {
            if(!$this->status || ($type != 'users' && $type != 'alliances')) return false;

            if($type == 'users') $index = 'username';
            else $index = 'tag';

            return sqlite_query($this->connection, "DELETE FROM highscores_".$type." WHERE ".$index." = '".sqlite_escape_string($id)."';");
        }

        function getList($type, $from, $to, $sort_field=false)
        {
            if(!$this->status || ($type != 'users' && $type != 'alliances')) return false;

            $allowed_sort_fields = array(
                'alliances' => array('scores_average', 'scores_total'),
                'users' => array('scores')
            );

            if($sort_field === false) $sort_field = array_shift($allowed_sort_fields[$type]);
            elseif(!in_array($sort_field, $allowed_sort_fields[$type])) return false;

            if($from > $to) list($from, $to) = array($to, $from);
            $from--;

            return sqlite_array_query($this->connection, "SELECT * FROM highscores_".$type." ORDER BY ".$sort_field." DESC,changed ASC LIMIT ".$from.", ".($to-$from-1).";", SQLITE_ASSOC);
        }

        function getCount($type, $highscores_file=false)
        {
            if($type != 'users' && $type != 'alliances') return false;

            $connection = false;
            if($highscores_file !== false)
            {
                if(is_file($highscores_file))
                    $connection = sqlite_open($highscores_file);
            }
            elseif(isset($this) && $this->status)
                $connection = &$this->connection;

            if(!$connection) return false;

            $result = sqlite_single_query($connection, "SELECT count(*) FROM highscores_".$type.";", true);

            if($highscores_file !== false)
                sqlite_close($connection);

            return $result;
        }

        function getPosition($type, $id, $sort_field=false)
        {
            if(!$this->status || ($type != 'users' && $type != 'alliances')) return false;

            if($type == 'users') $index = 'username';
            else $index = 'tag';

            $allowed_sort_fields = array(
                'alliances' => array('scores_average', 'scores_total'),
                'users' => array('scores')
            );

            if($sort_field === false) $sort_field = array_shift($allowed_sort_fields[$type]);
            elseif(!in_array($sort_field, $allowed_sort_fields[$type])) return false;

            # Zuerst Punkte herausfinden
            $query = sqlite_query($this->connection, "SELECT ".$sort_field.",changed FROM highscores_".$type." WHERE ".$index." = '".sqlite_escape_string($id)."' LIMIT 1;");
            if(!$query) return false;
            list($scores, $changed) = sqlite_fetch_array($query, SQLITE_NUM);

            # Wieviele Spieler sind von den Punkten her darueber?
            $above = sqlite_single_query($this->connection, "SELECT COUNT(*) FROM highscores_".$type." WHERE ".$sort_field." > '".sqlite_escape_string($scores)."';", true);

            # Wieviele Spieler haben die gleiche Punktzahl, aber hatten diese frueher?
            $above += sqlite_single_query($this->connection, "SELECT COUNT(*) FROM highscores_".$type." WHERE ".$sort_field." = '".sqlite_escape_string($scores)."' AND changed < '".sqlite_escape_string($changed)."';", true);

            return ($above+1);
        }
            function destroy()
                  {
                      if(!$this->status) return false;
          
                      return (sqlite_query($this->connection, "DELETE FROM highscores_users;") && sqlite_query($this->connection, "DELETE FROM highscores_alliances;"));
                  }
    }
?>