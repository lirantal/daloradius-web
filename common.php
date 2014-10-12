<?php

$testimonials = array(
	'I have been looking at a number of hotspot packages like Hotcakes, WiFiRoute and phpMyPrePaid but have ditched those for DaloRADIUS.  Really like the look of the DaloRADIUS and was the easiest to get working, looks very polished... January 2009 Garry Knapper, ConnectionServices.com',
	'Liran has developed a great product in daloRADIUS! I came across the daloRADIUS software while browsing the web for a web gui to control freeradius to handle our client base on our Wireless Network. At this stage we are currently still trialing the software to see if it would be suited to our purpose. We had a few problems after installation and a lot of questions which to my delighted surprise were answered by Liran promt and precise. We wish Liran a great success with this wonderful product and thank him for his expertise and support. We will continue to deal with Liran hopefully to achive a product that is 100% suited to our needs. June 19, 2008 Clint Armstrong, Director, Sprintlink (Pty) Ltd',
	'Liran is an extremely dedicated customer friendly designer and developer. Few, with such technical expertise have such a client friendly outlook. June 19, 2008 Rob Fowler, Director, Mianos International',
	'DaloRadius is very nice work of Liran, I appreciate all the efforts invested by Liran in this project. July 15, 2008 Jan Zorz, Head pragmatic, Pragma s.p.',
	'Liran is a hard worker who always keeps in mind to do things right. He uses intelligently the power and opportunities which open software may bring. It is a great pleasure to work with him, as it is always a very smooth experience. July 21, 2008 Enrique José Hernández, Analyst and developer, Warp Network S.L.',
	'Liran\'s collaborative effort to create a workable RADIUS GUI with others has proven invaluable to me and my company. The responses from him and others in the project have made the difference between frustration and peace. I recommend him and his project to anyone, and do so eagerly.” June 19, 2008 Peter Petroff, Sr. Network Engineer, Clearvoice Telecom',
	'First of all your RADIUS web management application is best ever i seen. Ismail Azmi',
	'Netphiles is an ISP in Tallahassee, FL.  We need a billing system that works with a Radius server, and that does recurrent billing and can use Paypal or Authorize.net.  DaloRadius seems to fit our needs.Richard Buchanan, Netphiles ISP',
	'nice job for creating daloradius interface.. very easy user management. Muhamad Hairul Ibrahim',
	'I\'m using Daloradius since December 2007. I feel very comfortable with DaloR and we haven\'t any problems. It\'s very stable, friendly interface and easy to mantain. I haven\'t bad comments to DaloR! I\'m working with Daloradius and Mikrotik and works fine. Nice Job man! All works fine! I\'ll post this solution in Mikrotik forum to recomend Daloradius. Thank you very much!. Pablo Lucchetti',
	'Thanks for a great product so far! Overall I am very excited. Dialup admin is a bit… antiquated.  Thanks for the hard work. Ben Wiechman, Network Admin, Wisper High Speed Internet',
	'I liked the solution and had good experience :). Tomasz Zieleniewski, Telecommunication Team Lead Architect, Poland, Warsaw, www http://touk.pl',
	'I was looking around for frontend for Freeradius and found your software (otherwise I was planning to write my own..). Great software! Jacques Pietersen, South Africa',
	'Great product..just what I needed. Norman Visagie',
	'Thanks for a great program. Bill Rothwell, Technical, FramBroadband WisP',
);

$daloradiusRss = simplexml_load_file('http://enginx.com/tag/daloradius/feed');
$daloradiusRssFeed = array();
foreach ($daloradiusRss->channel->item as $feed) {
	$daloradiusRssFeed[] = array(
		'title' => $feed->title, 
		'link' => $feed->link, 
		'date' => $feed->pubDate
	);
}