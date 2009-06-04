<?php
    class Galaxy
    {
        private $status = false;
        private $file_pointer = false;
        private $cache = array();
        private $filesize = false;
        private $filename = false;
        private $galaxy = false;
        protected $readonly = true;

        function __construct($galaxy, $write=true)
        {
            $this->filename = global_setting("DB_UNIVERSE").'/'.$galaxy;
            $this->galaxy = $galaxy;
            $this->readonly = !$write;
            if(is_file($this->filename) && is_readable($this->filename))
            {
                $this->filesize = $filesize = filesize($this->filename);
                if($write && is_writeable($this->filename))
                {
                    $this->file_pointer = fopen($this->filename, 'r+');
                    if(!fancy_flock($this->file_pointer, LOCK_EX))
                        $this->status = false;
                    else $this->status = 1;
                }
                else
                {
                    $this->file_pointer = fopen($this->filename, 'r');
                    fancy_flock($this->file_pointer, LOCK_SH);
                    $this->status = 2;
                }
            }
        }

        function __destruct()
        {
            if($this->status)
            {
                flock($this->file_pointer, LOCK_UN);
                fclose($this->file_pointer);
                $this->status = false;
            }
        }

        function getName() # For Instances
        {
            return $this->galaxy;
        }

        function getStatus()
        {
            return $this->status;
        }

        function readonly() { return $this->readonly; }

        function getSystemsCount()
        {
            if(!$this->status) return false;
            return 999;
        }

        private function seekSystem($system)
        {
            if(!$this->status) return false;

            $system = (int) $system;
            if($system < 1) return false;

            $pos = ($system-1)*1655;
            if($this->filesize < $pos+1655) return false; # System existiert nicht

            fseek($this->file_pointer, $pos, SEEK_SET);
            return true;
        }

        function getPlanetsCount($system)
        {
            if(!$this->status) return false;

            $system = (int) $system;

            if(!isset($this->cache['getPlanetsCount'])) $this->cache['getPlanetsCount'] = array();
            if(!isset($this->cache['getPlanetsCount'][$system]))
            {
                if(!$this->seekSystem($system)) return false;
                $this->cache['getPlanetsCount'][$system] = (ord(fread($this->file_pointer, 1))>>3)+10;
            }
            return $this->cache['getPlanetsCount'][$system];
        }

        function _getPlanetOwner($system, $planet)
        {
            if(!$this->status) return false;

            $planet = (int) $planet;
            $system = (int) $system;

            if(!isset($this->cache['getPlanetOwner'])) $this->cache['getPlanetOwner'] = array();
            if(!isset($this->cache['getPlanetOwner'][$system])) $this->cache['getPlanetOwner'][$system] = array();
            if(!isset($this->cache['getPlanetOwner'][$system][$planet]))
            {
                $planets_count = $this->getPlanetsCount($system);
                if(!$planets_count) return false;
                if($planet > $planets_count || $planet < 1) return false;

                if(!$this->seekSystem($system)) return false;

                fseek($this->file_pointer, 35+($planet-1)*24, SEEK_CUR);
                $this->cache['getPlanetOwner'][$system][$planet] = trim(fread($this->file_pointer, 24));
            }
            return $this->cache['getPlanetOwner'][$system][$planet];
        }

        function getPlanetOwner($system, $planet)
        {
            if(!$this->status) return false;

            $owner = $this->_getPlanetOwner($system, $planet);
            if(!$owner) return $owner;
            return preg_replace('/ \([Ug]\)$/', '', $owner);
        }

        function getPlanetOwnerFlag($system, $planet)
        {
            if(!$this->status) return false;

            $owner = $this->_getPlanetOwner($system, $planet);
            if($owner === false) return false;
            elseif(!$owner) return '';
            if(preg_match('/ \(([Ug])\)$/', $owner, $result))
                return $result[1];
            else return '';
        }

        function _setPlanetOwner($system, $planet, $owner)
        {
            if($this->status != 1) return false;

            $system = (int) $system;
            $planet = (int) $planet;
            $owner = trim(substr($owner, 0, 24));

            $planets_count = $this->getPlanetsCount($system);
            if(!$planets_count || $planet > $planets_count || $planet < 1) return false;

            if(!$this->seekSystem($system)) return false;

            fseek($this->file_pointer, 35+($planet-1)*24, SEEK_CUR);
            if(strlen($owner) > 0 && !fwrite($this->file_pointer, $owner)) return false;
            if(strlen($owner) < 24) fwrite($this->file_pointer, str_repeat(' ', 24-strlen($owner)));

            if(!isset($this->cache['getPlanetOwner'])) $this->cache['getPlanetOwner'] = array();
            if(!isset($this->cache['getPlanetOwner'][$system])) $this->cache['getPlanetOwner'][$system] = array();
            $this->cache['getPlanetOwner'][$system][$planet] = $owner;
            return true;
        }

        function setPlanetOwner($system, $planet, $owner)
        {
            if(!$this->status) return false;

            $owner = trim(substr($owner, 0, 20));
            $flag = $this->getPlanetOwnerFlag($system, $planet);
            if($flag) $owner .= ' ('.$flag.')';
            return $this->_setPlanetOwner($system, $planet, $owner);
        }

        function setPlanetOwnerFlag($system, $planet, $flag)
        {
            if(!$this->status) return false;

            $flag = substr($flag, 0, 1);
            $owner = $this->getPlanetOwner($system, $planet);
            if($flag) $owner .= ' ('.$flag.')';
            return $this->_setPlanetOwner($system, $planet, $owner);
        }

        function getPlanetName($system, $planet)
        {
            if(!$this->status) return false;

            $planet = (int) $planet;
            $system = (int) $system;

            if(!isset($this->cache['getPlanetName'])) $this->cache['getPlanetName'] = array();
            if(!isset($this->cache['getPlanetName'][$system])) $this->cache['getPlanetName'][$system] = array();
            if(!isset($this->cache['getPlanetName'][$system][$planet]))
            {
                $planets_count = $this->getPlanetsCount($system);
                if(!$planets_count) return false;
                if($planet > $planets_count || $planet < 1) return false;

                if(!$this->seekSystem($system)) return false;

                fseek($this->file_pointer, 755+($planet-1)*24, SEEK_CUR);
                $this->cache['getPlanetName'][$system][$planet] = trim(fread($this->file_pointer, 24));
            }
            return $this->cache['getPlanetName'][$system][$planet];
        }

        function setPlanetName($system, $planet, $name)
        {
            if($this->status != 1) return false;

            $system = (int) $system;
            $planet = (int) $planet;
            $name = trim(substr($name, 0, 24));

            $planets_count = $this->getPlanetsCount($system);
            if(!$planets_count || $planet > $planets_count || $planet < 1) return false;

            if(!$this->seekSystem($system)) return false;

            fseek($this->file_pointer, 755+($planet-1)*24, SEEK_CUR);
            if(strlen($name) > 0 && !fwrite($this->file_pointer, $name)) return false;
            if(strlen($name) < 24) fwrite($this->file_pointer, str_repeat(' ', 24-strlen($name)));

            if(!isset($this->cache['getPlanetName'])) $this->cache['getPlanetName'] = array();
            if(!isset($this->cache['getPlanetName'][$system])) $this->cache['getPlanetName'][$system] = array();
            $this->cache['getPlanetName'][$system][$planet] = $name;
            return true;
        }

        function getPlanetOwnerAlliance($system, $planet)
        {
            if(!$this->status) return false;

            $planet = (int) $planet;
            $system = (int) $system;

            if(!isset($this->cache['getPlanetOwnerAlliance'])) $this->cache['getPlanetOwnerAlliance'] = array();
            if(!isset($this->cache['getPlanetOwnerAlliance'][$system])) $this->cache['getPlanetOwnerAlliance'][$system] = array();
            if(!isset($this->cache['getPlanetOwnerAlliance'][$system][$planet]))
            {
                $planets_count = $this->getPlanetsCount($system);
                if(!$planets_count) return false;
                if($planet > $planets_count || $planet < 1) return false;

                if(!$this->seekSystem($system)) return false;

                fseek($this->file_pointer, 1475+($planet-1)*6, SEEK_CUR);
                $this->cache['getPlanetOwnerAlliance'][$system][$planet] = trim(fread($this->file_pointer, 6));
            }
            return $this->cache['getPlanetOwnerAlliance'][$system][$planet];
        }

        function setPlanetOwnerAlliance($system, $planet, $alliance)
        {
            if($this->status != 1) return false;

            $system = (int) $system;
            $planet = (int) $planet;
            $alliance = trim(substr($alliance, 0, 6));

            $planets_count = $this->getPlanetsCount($system);
            if(!$planets_count || $planet > $planets_count || $planet < 1) return false;

            if(!$this->seekSystem($system)) return false;

            fseek($this->file_pointer, 1475+($planet-1)*6, SEEK_CUR);
            if(strlen($alliance) > 0 && !fwrite($this->file_pointer, $alliance)) return false;
            if(strlen($alliance) < 6) fwrite($this->file_pointer, str_repeat(' ', 6-strlen($alliance)));

            if(!isset($this->cache['getPlanetOwnerAlliance'])) $this->cache['getPlanetOwnerAlliance'] = array();
            if(!isset($this->cache['getPlanetOwnerAlliance'][$system])) $this->cache['getPlanetOwnerAlliance'][$system] = array();
            $this->cache['getPlanetOwnerAlliance'][$system][$planet] = $alliance;
            return true;
        }

        function getPlanetSize($system, $planet)
        {
            if(!$this->status) return false;

            $planet = (int) $planet;
            $system = (int) $system;

            if(!isset($this->cache['getPlanetSize'])) $this->cache['getPlanetSize'] = array();
            if(!isset($this->cache['getPlanetSize'][$system])) $this->cache['getPlanetSize'][$system] = array();
            if(!isset($this->cache['getPlanetSize'][$system][$planet]))
            {
                $planets_count = $this->getPlanetsCount($system);
                if(!$planets_count) return false;
                if($planet > $planets_count || $planet < 1) return false;

                if(!$this->seekSystem($system)) return false;

                $bit_position = 5+($planet-1)*9;
                $byte_position = $bit_position%8;
                fseek($this->file_pointer, ($bit_position-$byte_position)/8, SEEK_CUR);
                $bytes = (ord(fread($this->file_pointer, 1)) << 8) | ord(fread($this->file_pointer, 1));
                $bytes = $bytes & ((1 << (16-$byte_position))-1);
                $bytes = $bytes >> (7-$byte_position);
                $bytes += 100;
                $this->cache['getPlanetSize'][$system][$planet] = $bytes;
            }
            return $this->cache['getPlanetSize'][$system][$planet];
        }

        function setPlanetSize($system, $planet, $size)
        {
            return true; # UNTESTED!!!

            if($this->status != 1) return false;

            $system = (int) $system;
            $planet = (int) $planet;
            $size = (int) $size;
            if($size < 100 || $size > 500) return false;
            $size -= 100;

            $planets_count = $this->getPlanetsCount($system);
            if(!$planets_count) return false;
            if($planet > $planets_count || $planet < 1) return false;

            if(!$this->seekSystem($system)) return false;

            $bit_position = 5+($planet-1)*9;
            $byte_position = $bit_position%8;
            fseek($this->file_pointer, $bit_position-$byte_position, SEEK_CUR);

            $byte1 = ord(fread($this->file_pointer, 1));
            $byte1 -= $byte1%(1<<(8-$byte_position));
            $byte1 = $byte1 | ($size>>$byte_position);

            $byte2 = ord(fread($this->file_pointer, 1));
            $byte2 = $byte2 & ((1<<(6-$byte_position))-1);
            $byte2 = $byte2 | ($size - ($size%(1<<$byte_position)));

            fseek($this->file_pointer, -2, SEEK_CUR);
            if(!fwrite($this->file_pointer, chr($byte1).chr($byte2))) return false;

            if(!isset($this->cache['getPlanetSize'])) $this->cache['getPlanetSize'] = array();
            if(!isset($this->cache['getPlanetSize'][$system])) $this->cache['getPlanetSize'][$system] = array();
            $this->cache['getPlanetSize'][$system][$planet] = $this->cache['getPlanetSize'][$system][$planet] = array();
        }

        function resetPlanet($system, $planet)
        {
            if(!$this->status) return false;

            return ($this->setPlanetName($system, $planet, '') && $this->_setPlanetOwner($system, $planet, '')
            && $this->setPlanetOwnerAlliance($system, $planet, '') && $this->setPlanetSize($system, $planet, rand(100, 500)));
        }

        function getPlanetClass($system, $planet)
        {
            if(!$this->status) return false;

            return getPlanetClass($this->galaxy, $system, $planet);
        }
    }

    function getGalaxiesCount()
    {
        for($i=0; is_file(global_setting("DB_UNIVERSE").'/'.($i+1)) && is_readable(global_setting("DB_UNIVERSE").'/'.($i+1)); $i++);
        return $i;
    }

    function getPlanetClass($galaxy, $system, $planet)
    {
        $type = (((floor($system/100)+1)*(floor(($system%100)/10)+1)*(($system%10)+1))%$planet)*$planet+($system%(($galaxy+1)*$planet));
        return $type%20+1;
    }
?>
