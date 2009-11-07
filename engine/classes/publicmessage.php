<?php

class PublicMessage extends Dataset
{
	protected $datatype = 'public_message';

	function publicMessageExists($name)
	{
		return (is_file(global_setting("DB_MESSAGES_PUBLIC").'/'.urlencode($name)) && is_readable(global_setting("DB_MESSAGES_PUBLIC").'/'.urlencode($name)));
	}

	function create()
	{
		if(file_exists($this->filename)) return false;
		$this->raw = array('last_view' => time());
		$this->write(true);
		$this->__construct($this->name);
		return true;
	}

	function __construct($name=false)
	{
		$this->save_dir = global_setting("DB_MESSAGES_PUBLIC");
		parent::__construct($name);
		if($this->status)
		{
			$this->raw['last_view'] = time();
			$this->changed = true;
		}
	}

	function createFromMessage($message)
	{
		if(!$this->create()) return false;

		$html = $message->html();
		$this->html($html);
		$text = $message->rawText();
		if($html)
		{
			$text = preg_replace('/ ?<span class="koords">.*?<\\/span>/', '', $text);
			#$text = preg_replace('/ ?<span class="angreifer-name">.*?<\\/span>/', 'Ein Angreifer', $text);
			#$text = preg_replace('/ ?<span class="verteidiger-name">.*?<\\/span>/', 'Ein Verteidiger', $text);
		}
		$this->text($text);

		$this->subject($message->subject());
		$this->time($message->getTime());
		$this->from($message->from());

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

		if(!isset($this->raw['text'])) $this->raw['parsed'] = '';
		elseif($this->html()) $this->raw['parsed'] = $this->raw['text'];
		else $this->raw['parsed'] = parse_html($this->raw['text']);
		$this->changed = true;
		return true;
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

	function type($type=false)
	{
		if(!$this->status) return false;

		if($type === false)
		{
			if(!isset($this->raw['type'])) return false;
			else return $this->raw['type'];
		}

		$this->raw['type'] = $type;
		$this->changed = true;
		return true;
	}


	function time($time=false)
	{
		if(!$this->status) return false;

		if($time === false)
		{
			if(!isset($this->raw['time'])) return false;
			else return $this->raw['time'];
		}

		$this->raw['time'] = $time;
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

	function getLastViewTime()
	{
		if(!$this->status)
		{
			return false;
		}

		return $this->raw['last_view'];
	}

	protected function getDataFromRaw(){}
	protected function getRawFromData(){}
}

?>


