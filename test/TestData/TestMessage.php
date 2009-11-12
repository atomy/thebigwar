<?php

class TestMessage
{
	private $text;
	private $subject;
	private $from;
	
	function __construct()
	{
		$this->subject = false;
		$this->text = false;
		$this->from = false;
	}
	
	function setFrom($from)
	{
		$this->from = $from;
	}
	
	function getFrom()
	{
		return $this->from;
	}
	
	function setText($txt)
	{
		$this->text = $txt;
	}
	
	function getText()
	{
		return $this->text;
	}
	
	function setSubject($subj)
	{
		$this->subject = $subj;
	}
	
	function getSubject()
	{
		return $this->subject;
	}
	
	function setType($type)
	{
		$this->type = $type;
	}
	
	function getType()
	{
		return $this->type;
	}
}

?>