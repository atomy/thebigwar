<?php
	$LOGIN = true;
	require_once( '../include/config_inc.php' );
	require( TBW_ROOT.'engine/include.php' );

	header('Content-type: text/javascript; charset=ISO-8859-1');
	header('Cache-control: max-age=152800');
	header('Expires: '.strftime('%a, %d %b %Y %T %Z', time()+152800));
?>
function set_time_globals(server_time)
{
	window.local_time_obj = new Date();
	window.local_time = Math.round(local_time_obj.getTime() / 1000);
	window.time_diff = local_time-server_time;

	window.countdowns = new Array();
}

function mk2(string)
{
	string = ''+string;
	while(string.length < 2)
		string = '0'+string;

	return string;
}

function time_up()
{
	local_time_up = new Date();
	server_time_up = new Date(local_time_up.getTime() - time_diff*1000);
	document.getElementById('time-local').firstChild.data = mk2(local_time_up.getHours())+':'+mk2(local_time_up.getMinutes())+':'+mk2(local_time_up.getSeconds());
	document.getElementById('time-server').firstChild.data = mk2(server_time_up.getHours())+':'+mk2(server_time_up.getMinutes())+':'+mk2(server_time_up.getSeconds());

	for(var codo_key in countdowns)
	{
		var codo = countdowns[codo_key];
		if(!codo[0] || !codo[1])
			continue;
		var this_remain = Math.round((codo[1]+time_diff)-local_time_up.getTime()/1000);

		if(this_remain < -codo[2])
		{
			while(document.getElementById('restbauzeit-'+codo[0]).firstChild)
				document.getElementById('restbauzeit-'+codo[0]).removeChild(document.getElementById('restbauzeit-'+codo[0]).firstChild);
			var link_fertig = document.createElement('a');
			link_fertig.setAttribute('href', '?'+window.session_cookie+'='+encodeURIComponent(window.session_id));
			link_fertig.className = 'fertig';
			link_fertig.setAttribute('title', 'Seite neu laden.');
			link_fertig.appendChild(document.createTextNode('Fertig.'));
			document.getElementById('restbauzeit-'+codo[0]).appendChild(link_fertig);
			delete countdowns[codo_key];
			continue;
		}

		if(this_remain < 0) this_remain = 0;

		var this_timestring = '';
		if(this_remain >= 86400)
		{
			this_timestring += Math.floor(this_remain/86400)+'\u2009d ';
			this_remain = this_remain % 86400;
		}

		this_timestring += mk2(Math.floor(this_remain/3600))+':'+mk2(Math.floor((this_remain%3600)/60))+':'+mk2(Math.floor(this_remain%60));

		document.getElementById('restbauzeit-'+codo[0]).firstChild.data = this_timestring;
	}
}

function init_countdown(obj_id, f_time)
{
	var show_cancel = true;
	if(init_countdown.arguments.length >= 3 && !init_countdown.arguments[2])
		show_cancel = false;
	var sleep_seconds = 0;
	if(init_countdown.arguments.length >= 4)
		sleep_seconds = init_countdown.arguments[3];

	var title_string = 'Fertigstellung: ';
	var local_date = new Date((f_time+time_diff)*1000);
	title_string += mk2(local_date.getHours())+':'+mk2(local_date.getMinutes())+':'+mk2(local_date.getSeconds())+', '+local_date.getFullYear()+'-'+mk2(local_date.getMonth()+1)+'-'+mk2(local_date.getDate())+' (Lokalzeit); ';

	var remote_date = new Date(f_time*1000);
	title_string += mk2(remote_date.getHours())+':'+mk2(remote_date.getMinutes())+':'+mk2(remote_date.getSeconds())+', '+remote_date.getFullYear()+'-'+mk2(remote_date.getMonth()+1)+'-'+mk2(remote_date.getDate())+' (Serverzeit)';

	document.getElementById('restbauzeit-'+obj_id).setAttribute('title', title_string);

	while(document.getElementById('restbauzeit-'+obj_id).firstChild)
		document.getElementById('restbauzeit-'+obj_id).removeChild(document.getElementById('restbauzeit-'+obj_id).firstChild);

	document.getElementById('restbauzeit-'+obj_id).appendChild(document.createTextNode('.'));

	if(show_cancel)
	{
		var cancel_link = document.createElement('a');
		cancel_link.setAttribute('href', '?cancel='+encodeURIComponent(obj_id)+'&'+window.session_cookie+'='+encodeURIComponent(window.session_id));
		cancel_link.className = 'abbrechen';
		cancel_link.appendChild(document.createTextNode('Abbrechen'));
		document.getElementById('restbauzeit-'+obj_id).appendChild(cancel_link);
	}

	window.countdowns.push(new Array(obj_id, f_time, sleep_seconds));

	time_up();
}

function ths(old_count)
{
	var minus = false;
	if(old_count < 0)
	{
		old_count *= -1;
		minus = true;
	}
	var count = new String(Math.floor(old_count));
	var new_count = new Array();
	var first_letters = count.length%3;
	if(first_letters == 0)
		first_letters = 3;
	new_count.push(count.substr(0, first_letters));
	var max_i = (count.length-first_letters)/3;
	for(var i=0; i < max_i; i++)
		new_count.push(count.substr(i*3+first_letters, 3));
	new_count = new_count.join("<?=utf8_jsentities(global_setting("THS_UTF8"))?>");
	if(minus)
		new_count = "\u2212"+new_count;
	return new_count;
}

function myParseInt(string)
{
	var count = parseInt(string);
	if(isNaN(count) || count < 0)
		return 0;
	else
		return count;
}

key_elements = new Array();
check_key_elements = new Array('a', 'input', 'button', 'textarea', 'select');

function get_key_elements()
{
	var key_els;
	var accesskey;
	for(var i=0; i < check_key_elements.length; i++)
	{
		key_els = document.getElementsByTagName(check_key_elements[i]);
		for(var j=0; j < key_els.length; j++)
		{
			accesskey = key_els[j].getAttribute('accesskey');
			if(accesskey)
				key_elements[accesskey] = key_els[j];
		}
	}

	document.onkeyup = key_event;
}

function key_event(e)
{
	if(!e) e = window.event;

	if(e.target) node = e.target;
	else if (e.srcElement) node = e.srcElement;
	if(node.nodeName.toLowerCase() == "textarea" || (node.nodeName.toLowerCase() == "input" && node.getAttribute("type") != "checkbox" && node.getAttribute("type") != "radio") || node.nodeName.toLowerCase() == "select") return true;

	if(e.altKey || e.ctrlKey)
		return true;

	var num;
	if(e.which) num = e.which;
	else if(e.keyCode) num = e.keyCode;
	else return true;

	var chr = String.fromCharCode(num).toLowerCase();

	if(!key_elements[chr])
		return true;
	else
	{
		key_elements[chr].focus();

		var that_href;
		if(key_elements[chr].nodeName.toLowerCase() == "button")
			key_elements[chr].click();
		else if((key_elements[chr].nodeName.toLowerCase() == "a" || key_elements[chr].nodeName.toLowerCase() == "link") && (that_href = key_elements[chr].getAttribute("href")))
			location.href = that_href;
		else if(key_elements[chr].nodeName.toLowerCase() == "input" && (key_elements[chr].getAttribute("type") == "checkbox" || key_elements[chr].getAttribute("type") == "radio"))
			key_elements[chr].checked = !key_elements[chr].checked;

		if(key_elements[chr].onclick)
			key_elements[chr].onclick();
	}
}

function load_titles()
{
	var js_title = document.createElement('div');
	js_title.setAttribute('id', 'js-title');
	js_title.style.position = 'absolute';
	js_title.appendChild(document.createTextNode('.'));
	js_title.className = 'hidden';
	document.getElementsByTagName('body')[0].appendChild(js_title);

	set_titles(document.getElementsByTagName('html')[0], 0);
}

var this_node = new Array();
var this_title = '';
var last_show_element;
var last_event_timeout;

function show_title(ev)
{
	if(!ev) ev = window.event;

	var el = ev.target;
	if(!el)
		el = ev.srcElement;
	last_show_element = el;
	if(el)
	{
		var this_title = el.getAttribute('titleAttribute');
		if(this_title)
		{
			document.getElementById('js-title').firstChild.data = this_title;
			last_event_timeout = setTimeout('really_show_title()', 1000);
		}
	}
}

function really_show_title()
{
	if(last_show_element)
	{
		var this_title = last_show_element.getAttribute('titleAttribute');
		if(this_title)
			document.getElementById('js-title').className = 'show';
	}
}

function move_title(ev)
{
	if(!ev) ev = window.event;

	var x_val = ev.pageX;
	if(!x_val)
		x_val = ev.clientX;
	var y_val = ev.pageY;
	if(!y_val)
		y_val = ev.clientY;
	document.getElementById('js-title').style.top = (y_val+10)+'px';
	document.getElementById('js-title').style.left = (x_val+10)+'px';
}

function hide_title(ev)
{
	document.getElementById('js-title').firstChild.data = '';
	document.getElementById('js-title').className = 'hidden';

	if(last_event_timeout)
		clearTimeout(last_event_timeout);
}

var for_el;
var for_el_attr;

function set_titles(el, level)
{
	if(el.getAttribute)
	{
		this_title = el.getAttribute('title');
		if(!this_title)
		{
			if(el.nodeName.toLowerCase() == 'label')
			{
				for_el_attr = el.getAttribute('for');
				if(for_el_attr)
				{
					for_el = document.getElementById(for_el_attr);
					if(for_el)
						this_title = for_el.getAttribute('title');
				}
			}
		}

		if(this_title)
		{
			el.onmouseover = show_title;
			el.onmousemove = move_title;
			el.onmouseout = hide_title;

			el.setAttribute('titleAttribute', this_title);
			el.removeAttribute('title');
		}
	}

	this_node[level] = el.firstChild;
	var next_element;
	while(this_node[level] != null)
	{
		if(this_node[level].nodeType == 1)
			set_titles(this_node[level], level+1);

		this_node[level] = this_node[level].nextSibling;
	}
}

function refresh_ress(refresh_int, carbon_vorh, aluminium_vorh, wolfram_vorh, radium_vorh, tritium_vorh, carbon_prod, aluminium_prod, wolfram_prod, radium_prod, tritium_prod)
{
	window.carbon_vorh = carbon_vorh;
	window.aluminium_vorh = aluminium_vorh;
	window.wolfram_vorh = wolfram_vorh;
	window.radium_vorh = radium_vorh;
	window.tritium_vorh = tritium_vorh;

	window.carbon_prod = carbon_prod;
	window.aluminium_prod = aluminium_prod;
	window.wolfram_prod = wolfram_prod;
	window.radium_prod = radium_prod;
	window.tritium_prod = tritium_prod;

	var now_time = new Date();
	window.last_increase_ress = now_time.getTime();

	setInterval("increase_ress()", refresh_int);
}

function increase_ress()
{
	var now_time = new Date();
	var time_diff = (now_time.getTime()-window.last_increase_ress)*0.000000277777777777778;
	window.carbon_vorh += window.carbon_prod*time_diff;
	window.aluminium_vorh += window.aluminium_prod*time_diff;
	window.wolfram_vorh += window.wolfram_prod*time_diff;
	window.radium_vorh += window.radium_prod*time_diff;
	window.tritium_vorh += window.tritium_prod*time_diff;

	document.getElementById('ress-carbon').firstChild.data = ths(window.carbon_vorh);
	document.getElementById('ress-aluminium').firstChild.data = ths(window.aluminium_vorh);
	document.getElementById('ress-wolfram').firstChild.data = ths(window.wolfram_vorh);
	document.getElementById('ress-radium').firstChild.data = ths(window.radium_vorh);
	document.getElementById('ress-tritium').firstChild.data = ths(window.tritium_vorh);

	window.last_increase_ress = now_time.getTime();
}


fadeout_elements = new Array();
function popup_message(message, classn, calling_node)
{
	var timeout = 500;

	popup_el = document.createElement('p');
	if(classn) popup_el.className = 'popup '+classn;
	else popup_el.className = 'popup';

	popup_el.appendChild(document.createTextNode(message));

	posx = calling_node.offsetLeft+10;
	posy = calling_node.offsetTop+calling_node.offsetHeight+5;
	parent_offset = calling_node;
	while(parent_offset.offsetParent)
	{
		parent_offset = parent_offset.offsetParent;
		if(parent_offset.nodeName.toLowerCase() == 'body') break;
		posx += parent_offset.offsetLeft;
		posy += parent_offset.offsetTop;
	}

	popup_el.style.position = 'absolute';
	popup_el.style.top = posy+'px';
	popup_el.style.left = posx+'px';

	body_el = document.getElementsByTagName('body')[0];
	body_el.appendChild(popup_el);

	var right_point = popup_el.offsetLeft+popup_el.offsetWidth;
	var parent_width = body_el.offsetWidth;

	if(right_point > parent_width)
		popup_el.style.left = (parent_width-popup_el.offsetWidth)+'px';

	var array_key = fadeout_elements.length;
	fadeout_elements[array_key] = popup_el;
	setTimeout('popup_fadeout('+array_key+');', timeout);
}

function fast_action(node, action_type, galaxy, system, planet)
{
	var xmlhttp = new XMLHttpRequest();
	var request_url = '<?=h_root?>/login/scripts/ajax.php?action='+encodeURIComponent(action_type)+'&action_galaxy='+encodeURIComponent(galaxy)+'&action_system='+encodeURIComponent(system)+'&action_planet='+encodeURIComponent(planet)+'&'+encodeURIComponent(session_cookie)+'='+encodeURIComponent(session_id)+'&database='+encodeURIComponent(database_id);
	xmlhttp.open('GET', request_url, false);
	xmlhttp.send(null);

	if (xmlhttp.readyState == 4 && xmlhttp.status == 200 && xmlhttp.responseXML)
		popup_message(xmlhttp.responseXML.getElementsByTagName('result')[0].firstChild.data, xmlhttp.responseXML.getElementsByTagName('classname')[0].firstChild.data, node);

	return false;
}

function setOpacity(el_key,opacity)
{
	el = fadeout_elements[el_key];
	opacity = (opacity == 100)?99:opacity;
	// IE
	el.style.filter = "alpha(opacity:"+opacity+")";
	// Safari < 1.2, Konqueror
	el.style.KHTMLOpacity = opacity/100;
	// Old Mozilla
	el.style.MozOpacity = opacity/100;
	// Safari >= 1.2, Firefox and Mozilla, CSS3
	el.style.opacity = opacity/100
}

function popup_fadeout(el_key)
{
	el = fadeout_elements[el_key];

	steps = 50;
	timel = 4000;

	setOpacity(el_key,100); // To prevent flicker in Firefox
	                        // The first time the opacity is set
	                        // the element flickers in Firefox
	fadeStep = 100/steps;
	timeStep = timel/steps;
	opacity = 100;
	timel = 100;

	while (opacity >=0) {
		window.setTimeout("setOpacity("+el_key+","+opacity+")",timel);
		opacity -= fadeStep;
		timel += timeStep;
	}
	window.setTimeout('fadeout_elements['+el_key+'].parentNode.removeChild(el);', timel);
}

var loading_instances = 0;
var loading_element = false;

function add_loading_instance()
{
	if(!loading_element)
	{
		loading_element = document.createElement('p');
		loading_element.id = 'loading';
		loading_element.appendChild(document.createTextNode('Laden...'));
		document.getElementsByTagName('body')[0].appendChild(loading_element);
	}
	loading_instances++;
}

function remove_loading_instance()
{
	loading_instances--;
	if(loading_instances <= 0)
	{
		loading_element.parentNode.removeChild(loading_element);
		loading_element = false;
	}
}


users_list_timeout = false;
users_list = false;
users_list_selected = false;

function activate_users_list(element)
{
	element.onkeypress = make_users_list;
	element.setAttribute('autocomplete', 'off');

	// Opera fix
	element.style.position = 'relative';
}

function make_users_list(e)
{
	if(!e) e = window.event;
	if(e.target) node=e.target;
	else if(e.srcElement) node=e.srcElement;
	else return;

	if(users_list_timeout) clearTimeout(users_list_timeout);

	users_list_timeout = setTimeout('do_make_users_list(node)', 500);
	node.onblur = function(){clearTimeout(users_list_timeout);}
	node.onfocus = function(){make_users_list(e);}
}

function users_list_select(node, move_cursor)
{
	l = node.nextSibling;
	if(!l || l.className != 'autocomplete') return;

	if(typeof users_list_selected == 'boolean' && !users_list_selected) users_list_selected = -1;
	users_list_selected_old = users_list_selected;
	users_list_selected += move_cursor;

	if(users_list_selected < 0) users_list_selected = l.childNodes.length-1;
	else if(users_list_selected >= l.childNodes.length) users_list_selected = 0;

	if(users_list_selected_old >= 0 && l.childNodes[users_list_selected_old] && !userlist_active_before_keyboard)
		l.childNodes[users_list_selected_old].className = '';
	if(l.childNodes[users_list_selected])
	{
		window.userlist_active_before_keyboard = (l.childNodes[users_list_selected].className=='selected');
		l.childNodes[users_list_selected].className = 'selected';
		node.value = l.childNodes[users_list_selected].firstChild.data;
	}
	else
	{
		window.userlist_active_before_keyboard = false;
		users_list_selected = false;
	}
}

function do_make_users_list(node)
{
	if(node.value.length < <?=global_setting("LIST_MIN_CHARS")?>)
	{
		if(users_list)
		{
			users_list.parentNode.removeChild(users_list);
			users_list = false;
			users_list_selected = false;
		}
		return;
	}

	node.onblur = function(){t=this; setTimeout('if(users_list){users_list.parentNode.removeChild(users_list);users_list=false;users_list_selected=false;}',500);}
	node.onkeypress = function(e)
	{
		if(!e) e = window.event;

		if(!_SARISSA_IS_IE && !e.altKey && !e.ctrlKey && !e.shiftKey && !e.metaKey)
		{
			if((e.DOM_VK_DOWN && e.keyCode == e.DOM_VK_DOWN) || e.keyCode == 40)
			{
				users_list_select(node, 1);
				return false;
			}
			else if((e.DOM_VK_UP && e.keyCode == e.DOM_VK_UP) || e.keyCode == 38)
			{
				users_list_select(node, -1);
				return false;
			}
		}

		if((e.DOM_VK_RETURN && e.keyCode == e.DOM_VK_RETURN) || (e.DOM_VK_ENTER && e.keyCode == e.DOM_VK_ENTER) || e.keyCode == 13 || e.keyCode == 14)
		{
			if(users_list)
			{
				users_list.parentNode.removeChild(users_list);
				users_list = false;
				users_list_selected = false;
				return false;
			}
		}
		if((e.DOM_VK_TAB && e.keyCode == e.DOM_VK_TAB) || e.keyCode == 9)
		{
			if(users_list)
			{
				users_list.parentNode.removeChild(users_list);
				users_list = false;
				users_list_selected = false;
			}
		}

		make_users_list(e);
	}

	if(_SARISSA_IS_IE)
	{
		node.onkeyup = function(e)
		{
			if(!e) e = window.event;
			if(!e.altKey && !e.ctrlKey && !e.shiftKey && !e.metaKey)
			{
				if((e.DOM_VK_DOWN && e.keyCode == e.DOM_VK_DOWN) || e.keyCode == 40)
				{
					users_list_select(node, 1);
					return false;
				}
				else if((e.DOM_VK_UP && e.keyCode == e.DOM_VK_UP) || e.keyCode == 38)
				{
					users_list_select(node, -1);
					return false;
				}
			}
		}
	}

	old_l = false;
	if(users_list) old_l = users_list;

	l = document.createElement('ul');
	l.className = 'autocomplete';
	l.style.position = 'absolute';
	l.style.top = (node.offsetTop+node.offsetHeight)+'px';
	l.style.left = node.offsetLeft+'px';
	l.style.width = node.offsetWidth+'px';

	var xmlhttp = new XMLHttpRequest();
	var request_url = '<?=h_root?>/login/scripts/ajax.php?action=userlist&query='+encodeURIComponent(node.value)+'&'+encodeURIComponent(session_cookie)+'='+encodeURIComponent(session_id)+'&database='+encodeURIComponent(database_id);
	xmlhttp.open('GET', request_url, true);

	xmlhttp.onreadystatechange = function() {
		if (xmlhttp.readyState == 4) {
			if (xmlhttp.status == 200)
			{
				var results = xmlhttp.responseXML.getElementsByTagName('result');
				for(var i=0; i < results.length; i++)
				{
					v = results[i].firstChild.data;
					var next_li = document.createElement('li');
					next_li.onclick = function(){node.value = this.firstChild.data;if(users_list){users_list.parentNode.removeChild(users_list);users_list=false;users_list_selected=false;}}
					next_li.onmouseover = function(){window.userlist_active_before_mouse=(this.className=='selected');this.className = 'selected';}
					next_li.onmouseout = function(){if(!userlist_active_before_mouse)this.className = '';}
					next_li.appendChild(document.createTextNode(v));
					l.appendChild(next_li);
				}
				if(old_l && old_l.parentNode)
				{
					old_l.parentNode.removeChild(users_list);
					users_list = false;
					users_list_selected = false;
				}
				var do_insert = true;
				if(results.length <= 0) do_insert = false;
				else if(results.length == 1 && node.value.toLowerCase() == v.toLowerCase()) do_insert = false;

				if(do_insert)
				{
					node.parentNode.insertBefore(l, node.nextSibling);
					users_list = l;
				}
				else users_list = false;
			}
		}
	}

	xmlhttp.send(null);
}

alliances_list_timeout = false;
alliances_list = false;
alliances_list_selected = false;

function activate_alliances_list(element)
{
	element.onkeypress = make_alliances_list;
	element.setAttribute('autocomplete', 'off');

	// Opera fix
	element.style.position = 'relative';
}

function make_alliances_list(e)
{
	if(!e) e = window.event;
	if(e.target) node=e.target;
	else if(e.srcElement) node=e.srcElement;
	else return;

	if(alliances_list_timeout) clearTimeout(alliances_list_timeout);

	alliances_list_timeout = setTimeout('do_make_alliances_list(node)', 500);
	node.onblur = function(){clearTimeout(alliances_list_timeout);}
	node.onfocus = function(){make_alliances_list(e);}
}

function alliances_list_select(node, move_cursor)
{
	l = node.nextSibling;
	if(!l || l.className != 'autocomplete') return;

	if(typeof alliances_list_selected == 'boolean' && !alliances_list_selected) alliances_list_selected = -1;
	alliances_list_selected_old = alliances_list_selected;
	alliances_list_selected += move_cursor;

	if(alliances_list_selected < 0) alliances_list_selected = l.childNodes.length-1;
	else if(alliances_list_selected >= l.childNodes.length) alliances_list_selected = 0;

	if(alliances_list_selected_old >= 0 && l.childNodes[alliances_list_selected_old] && !alliancelist_active_before_keyboard)
		l.childNodes[alliances_list_selected_old].className = '';
	if(l.childNodes[alliances_list_selected])
	{
		window.alliancelist_active_before_keyboard = (l.childNodes[alliances_list_selected].className=='selected');
		l.childNodes[alliances_list_selected].className = 'selected';
		node.value = l.childNodes[alliances_list_selected].firstChild.data;
	}
	else
	{
		window.alliancelist_active_before_keyboard = false;
		alliances_list_selected = false;
	}
}

function do_make_alliances_list(node)
{
	if(node.value.length < <?=global_setting("LIST_MIN_CHARS")?>)
	{
		if(alliances_list)
		{
			alliances_list.parentNode.removeChild(alliances_list);
			alliances_list = false;
			alliances_list_selected = false;
		}
		return;
	}

	node.onblur = function(){t=this; setTimeout('if(alliances_list){alliances_list.parentNode.removeChild(alliances_list);alliances_list=false;alliances_list_selected=false;}',500);}
	node.onkeypress = function(e)
	{
		if(!e) e = window.event;

		if(!_SARISSA_IS_IE && !e.altKey && !e.ctrlKey && !e.shiftKey && !e.metaKey)
		{
			if((e.DOM_VK_DOWN && e.keyCode == e.DOM_VK_DOWN) || e.keyCode == 40)
			{
				alliances_list_select(node, 1);
				return false;
			}
			else if((e.DOM_VK_UP && e.keyCode == e.DOM_VK_UP) || e.keyCode == 38)
			{
				alliances_list_select(node, -1);
				return false;
			}
		}

		if((e.DOM_VK_RETURN && e.keyCode == e.DOM_VK_RETURN) || (e.DOM_VK_ENTER && e.keyCode == e.DOM_VK_ENTER) || e.keyCode == 13 || e.keyCode == 14)
		{
			if(alliances_list)
			{
				alliances_list.parentNode.removeChild(alliances_list);
				alliances_list = false;
				alliances_list_selected = false;
				return false;
			}
		}
		if((e.DOM_VK_TAB && e.keyCode == e.DOM_VK_TAB) || e.keyCode == 9)
		{
			if(alliances_list)
			{
				alliances_list.parentNode.removeChild(alliances_list);
				alliances_list = false;
				alliances_list_selected = false;
			}
		}

		make_alliances_list(e);
	}

	if(_SARISSA_IS_IE)
	{
		node.onkeyup = function(e)
		{
			if(!e) e = window.event;
			if(!e.altKey && !e.ctrlKey && !e.shiftKey && !e.metaKey)
			{
				if((e.DOM_VK_DOWN && e.keyCode == e.DOM_VK_DOWN) || e.keyCode == 40)
				{
					alliances_list_select(node, 1);
					return false;
				}
				else if((e.DOM_VK_UP && e.keyCode == e.DOM_VK_UP) || e.keyCode == 38)
				{
					alliances_list_select(node, -1);
					return false;
				}
			}
		}
	}

	old_l = false;
	if(alliances_list) old_l = alliances_list;

	l = document.createElement('ul');
	l.className = 'autocomplete';
	l.style.position = 'absolute';
	l.style.top = (node.offsetTop+node.offsetHeight)+'px';
	l.style.left = node.offsetLeft+'px';
	l.style.width = node.offsetWidth+'px';

	var xmlhttp = new XMLHttpRequest();
	var request_url = '<?=h_root?>/login/scripts/ajax.php?action=alliancelist&query='+encodeURIComponent(node.value)+'&'+encodeURIComponent(session_cookie)+'='+encodeURIComponent(session_id)+'&database='+encodeURIComponent(database_id);
	xmlhttp.open('GET', request_url, true);

	xmlhttp.onreadystatechange = function() {
		if (xmlhttp.readyState == 4) {
			if (xmlhttp.status == 200)
			{
				var results = xmlhttp.responseXML.getElementsByTagName('result');
				for(var i=0; i < results.length; i++)
				{
					v = results[i].firstChild.data;
					var next_li = document.createElement('li');
					next_li.onclick = function(){node.value = this.firstChild.data;if(alliances_list){alliances_list.parentNode.removeChild(alliances_list);alliances_list=false;alliances_list_selected=false;}}
					next_li.onmouseover = function(){window.alliancelist_active_before_mouse=(this.className=='selected');this.className = 'selected';}
					next_li.onmouseout = function(){if(!alliancelist_active_before_mouse)this.className = '';}
					next_li.appendChild(document.createTextNode(v));
					l.appendChild(next_li);
				}
				if(old_l && old_l.parentNode)
				{
					old_l.parentNode.removeChild(alliances_list);
					alliances_list = false;
					alliances_list_selected = false;
				}
				var do_insert = true;
				if(results.length <= 0) do_insert = false;
				else if(results.length == 1 && node.value.toLowerCase() == v.toLowerCase()) do_insert = false;

				if(do_insert)
				{
					node.parentNode.insertBefore(l, node.nextSibling);
					alliances_list = l;
				}
				else alliances_list = false;
			}
		}
	}

	xmlhttp.send(null);
}

preloaded_systems = new Array();
preloading_systems = new Array();
function preload_systems(systems)
{
	if(typeof systems != 'object')
	{
		pr_system = systems;
		systems = new Array();
		systems.push(pr_system);
	}

	request_url = '<?=h_root?>/login/scripts/ajax.php?action=universe&'+encodeURIComponent(session_cookie)+'='+encodeURIComponent(session_id)+'&database='+encodeURIComponent(database_id);
	var c = 0;
	for(var i=0; i < systems.length; i++)
	{
		if(typeof preloaded_systems[systems[i]] != 'undefined' || preloading_systems[systems[i]]) continue;
		request_url += '&system[]='+encodeURIComponent(systems[i]);
		preloading_systems[systems[i]] = true;
		c++;
	}

	if(c <= 0) return;

	add_loading_instance();

	var xmlhttp = new XMLHttpRequest();
	xmlhttp.open('GET', request_url, true);

	xmlhttp.onreadystatechange = function() {
		if (xmlhttp.readyState == 4)
		{
			if(xmlhttp.status == 200 && xmlhttp.responseXML)
			{
				system_results = xmlhttp.responseXML.getElementsByTagName('system');
				for(i=0; i < system_results.length; i++)
				{
					system_number = system_results[i].getAttribute('number');
					preloaded_systems[system_number] = new Array();
					system_info = system_results[i].childNodes;
					for(j=0; j < system_info.length; j++)
					{
						if(system_info[j].nodeType != 1) continue;
						planet_number = system_info[j].getAttribute('number');
						preloaded_systems[system_number][planet_number] = new Array();
						preloading_systems[system_number] = false;
						planet_infos = system_info[j].childNodes;
						if(planet_infos.length <= 0) continue;
						for(k=0; k < planet_infos.length; k++)
						{
							if(planet_infos[k].nodeType != 1) continue;
							var this_info = '';
							if(planet_infos[k].nodeName.toLowerCase() == 'truemmerfeld')
								this_info = new Array(planet_infos[k].getAttribute('carbon'), planet_infos[k].getAttribute('aluminium'), planet_infos[k].getAttribute('wolfram'), planet_infos[k].getAttribute('radium'));
							else if(planet_infos[k].childNodes.length > 0)
								this_info = planet_infos[k].firstChild.data;
							preloaded_systems[system_number][planet_number][planet_infos[k].nodeName.toLowerCase()] = this_info;
						}
					}
				}
				for(var i in systems)
				{
					if(typeof preloaded_systems[systems[i]] == 'undefined')
						preloaded_systems[systems[i]] = false;
				}
			}
			remove_loading_instance();
		}
	}

	xmlhttp.send(null);
}
