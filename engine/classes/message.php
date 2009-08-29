<?php
	class Message extends Dataset
	{
		protected $datatype = 'message';
		protected $im_check_notify = array();

		function __construct($name=false, $write=true)
		{
			$this->save_dir = global_setting("DB_MESSAGES");
			parent::__construct($name, $write);
		}

		function create()
		{
			if(file_exists($this->filename)) return false;
			$this->raw = array('time' => time());
			$this->write(true);
			$this->__construct($this->name);
			return true;
		}

		function text($text=false)
		{
			if(!$this->status) return false;

			if($text === false)
			{
				if(!isset($this->raw['text'])) return '';
				else
				{
					if(!isset($this->raw['parsed'])) $this->_createParsed();
					return $this->raw['parsed'];
				}
			}

			$this->raw['text'] = $text;
			$this->_createParsed();

			$this->changed = true;
			return true;
		}

		function _createParsed()
		{
			if(!$this->status) return false;

			if(!isset($this->raw['text']))
			{
				$this->raw['parsed'] = '';
			}
			elseif($this->html())
			{

				$this->raw['parsed'] = $this->raw['text'];

			}
			else
			{

				$this->raw['parsed'] = parse_html($this->raw['text']);

			}
			$this->changed = true;

			return true;
		}

		function rawText()
		{
			if(!$this->status) return false;

			if(!isset($this->raw['text'])) return '';
			else return $this->raw['text'];
		}

		function from($from=false)
		{
			if(!$this->status) return false;

			if($from === false)
			{
				if(!isset($this->raw['from'])) return '';
				else return $this->raw['from'];
			}

			$this->raw['from'] = $from;
			$this->changed = true;
			return true;
		}

              function to($to=false)
		{
			if(!$this->status) return false;

			if($to === false)
			{
				if(!isset($this->raw['to'])) return '';
				else return $this->raw['to'];
			}

			$this->raw['to'] = $to;
			$this->changed = true;
			return true;
		}


		function renameUser($old_name, $new_name)
		{
			if(!$this->status) return false;

			if($old_name == $new_name) return 2;

			if(isset($this->raw['from']) && $this->raw['from'] == $old_name)
			{
				$this->raw['from'] = $new_name;
				$this->changed = true;
			}
			if(isset($this->raw['users'][$old_name]))
			{
				$this->raw['users'][$new_name] = $this->raw['users'][$old_name];
				unset($this->raw['users'][$old_name]);
				$this->changed = true;
			}
			return true;
		}

		function subject($subject=false)
		{
			if(!$this->status) return false;

			if($subject === false)
			{
				if(!isset($this->raw['subject']) || trim($this->raw['subject']) == '') return 'Kein Betreff';
				else return $this->raw['subject'];
			}

			$this->raw['subject'] = $subject;
			$this->changed = true;
			return true;
		}

		function html($html=-1)
		{
			if(!$this->status) return false;

			if($html === -1)
			{
				if(!isset($this->raw['html'])) return false;
				else return $this->raw['html'];
			}

			$this->raw['html'] = (bool) $html;
			$this->_createParsed();
			$this->changed = true;
			return true;
		}

		function addUser($user, $type=6)
		{
			if(!$this->status) return false;

			if(!isset($this->raw['users']))
				$this->raw['users'] = array();
			if(isset($this->raw['users'][$user]))
				return false;

			$user_obj = Classes::User($user);
			if(!$user_obj->getStatus()) return false;
			$user_obj->addMessage($this->name, $type);
			unset($user_obj);

			$this->raw['users'][$user] = $type;
			$this->changed = true;

			if($type != 8)
				$this->im_check_notify[$user] = $type;

			return true;
		}

		function removeUser($user, $edit_user=true)
		{
			if(!$this->status) return false;

			if(!isset($this->raw['users']) || !isset($this->raw['users'][$user]))
				return 2;

			unset($this->raw['users'][$user]);
			$this->changed = true;

			if($edit_user)
			{
				$user = Classes::User($user);
				$type = $user->findMessageType($this->name);
				$user->removeMessage($this->name, $type, false);
			}

// this makes bunny cry, self execution however thats possible, 
// deactivated since it prevents deleting accounts
/*
			if(count($this->raw['users']) == 0)
			{
				if(!unlink($this->filename)) return false;
				else $this->status = false;
			}
*/

			return true;
		}

		function getTime()
		{
			if(!$this->status) return false;

			if(!isset($this->raw['time'])) return false;

			return $this->raw['time'];
		}

		function destroy()
		{
			if(!$this->status) return false;

			if(isset($this->raw['users']) && count($this->raw['users']) > 0)
			{
				foreach($this->raw['users'] as $user=>$type)
					$this->removeUser($user);
			}
			if($this->status)
			{
				if(!unlink($this->filename)) return false;
				else
				{
					$this->status = false;
					return true;
				}
			}
		}

		function getUsersList()
		{
			if(!$this->status) return false;

			return array_keys($this->raw['users']);
		}

		protected function getDataFromRaw(){}
		protected function getRawFromData()
		{
			if(count($this->im_check_notify) > 0)
			{
				global $message_type_names;
				$imfile = Classes::IMFile();
				foreach($this->im_check_notify as $user=>$type)
				{
					$user_obj = Classes::User($user);
					$im_settings = $user_obj->getNotificationType();
					if($im_settings)
					{
						$im_receive = $user_obj->checkSetting('messenger_receive');
						if($im_receive['messages'][$type])
							$imfile->addMessage($im_settings[0], $im_settings[1], $user, "Sie haben eine neue Nachricht der Sorte ".$message_type_names[$type].($this->from() ? " von ".$this->from() : '')." erhalten. Der Betreff lautet: ".$this->subject());
					}
					unset($this->im_check_notify[$user]);
				}
			}
		}
	}
?>