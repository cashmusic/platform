INSERT INTO `assets` (`id`, `user_id`, `parent_id`, `location`, `connection_id`, `title`, `description`, `public_status`, `creation_date`, `modification_date`)
VALUES (100, 1, 0, 'http://cashmusic.s3.amazonaws.com/permalink/demos/portugaltheman/WhenTheWarEnds_987yh.mp3', 0, '"When The World Ends" MP3', '320kbps MP3', 1, 1311928519, 0);



INSERT INTO `elements` (`id`, `user_id`, `name`, `type`, `options`, `license_id`, `creation_date`, `modification_date`)
VALUES (100, 1, 'Portugal. The Man email for download', 'emailcollection', '{"message_invalid_email":"That email address wasn’t valid. Try again.","message_privacy":"We won’t share, sell, or be jerks with your email address. Promise.","message_success":"Thanks! You’re all signed up. Here’s your download:","email_list_id":"100","asset_id":"100","comment_or_radio":null,"do_not_verify":"1"}', 0, 1295306566, 1315552494);

INSERT INTO `elements` (`id`, `user_id`, `name`, `type`, `options`, `license_id`, `creation_date`, `modification_date`)
VALUES (101, 1, 'Iron & Wine ticket contest', 'emailcollection', '{"message_invalid_email":"Sorry, that email address wasn’t valid. Please try again.","message_privacy":"Your email won’t be shared and we’ll use it respectfully. ","message_success":"Thanks! You’re all signed up. Winners will be emailed prior to the concert.","email_list_id":"101","asset_id":"0","comment_or_radio":null,"do_not_verify":"1"}', 0, 1315519963, NULL);

INSERT INTO `elements` (`id`, `user_id`, `name`, `type`, `options`, `license_id`, `creation_date`, `modification_date`)
VALUES (102, 1, 'Wild Flag tour dates', 'tourdates', '{"visible_event_types":"archive"}', 0, 1316730460, NULL);

INSERT INTO `elements` (`id`, `user_id`, `name`, `type`, `options`, `license_id`, `creation_date`, `modification_date`)
VALUES (103, 1, 'Palmer/Gaiman filtered social feeds', 'socialfeeds', '{"tumblr":[],"twitter":[{"twitterusername":"neilhimself","twitterhidereplies":false,"twitterfiltertype":"beginwith","twitterfiltervalue":"@amandapalmer"},{"twitterusername":"amandapalmer","twitterhidereplies":false,"twitterfiltertype":"beginwith","twitterfiltervalue":"@neilhimself"}],"post_limit":"40"}', 0, 1317978017, NULL);



INSERT INTO `people_lists` (`id`, `name`, `description`, `user_id`, `connection_id`, `creation_date`, `modification_date`)
VALUES (100, 'Portugal List', 'The demo list for Portugal. The Man’s email download demo.', 1, 0, 1311316210, 0);

INSERT INTO `people_lists` (`id`, `name`, `description`, `user_id`, `connection_id`, `creation_date`, `modification_date`)
VALUES (101, 'Iron & Wine List', 'The demo list for Iron & Wine’s email contest demo.', 1, 0, 1313045289, 0);



INSERT INTO `calendar_venues` (`id`, `name`, `address1`, `address2`, `city`, `region`, `country`, `postalcode`, `url`, `phone`, `creation_date`) 
VALUES (100,'Varsity Theatre','1308 4TH STREET SE','','Minneapolis','MN','USA','55414','http://www.varsitytheater.org/','(612) 604-0222',1316730630);

INSERT INTO `calendar_venues` (`id`, `name`, `address1`, `address2`, `city`, `region`, `country`, `postalcode`, `url`, `phone`, `creation_date`) 
VALUES (101,'Waiting Room','6212 Maple Street','','Omaha','NE','USA','68104','http://www.waitingroomlounge.com/','(402) 884-5353',1316730630);

INSERT INTO `calendar_venues` (`id`, `name`, `address1`, `address2`, `city`, `region`, `country`, `postalcode`, `url`, `phone`, `creation_date`) 
VALUES (102,'Record Bar','1020 Westport Road','','Kansas City','MO','USA','64111','http://www.therecordbar.com/','(816) 753-5207',1316730630);

INSERT INTO `calendar_venues` (`id`, `name`, `address1`, `address2`, `city`, `region`, `country`, `postalcode`, `url`, `phone`, `creation_date`) 
VALUES (103,'High Dive ','51 East Main Street','','Champaign','IL','USA','61820','http://thehighdive.com/','(217) 356-2337',1316730630);

INSERT INTO `calendar_venues` (`id`, `name`, `address1`, `address2`, `city`, `region`, `country`, `postalcode`, `url`, `phone`, `creation_date`) 
VALUES (104,'Empty Bottle','1035 N Western Ave','','Chicago','IL','USA','60622','http://www.emptybottle.com/','(773) 276-3600',1316730630);

INSERT INTO `calendar_venues` (`id`, `name`, `address1`, `address2`, `city`, `region`, `country`, `postalcode`, `url`, `phone`, `creation_date`) 
VALUES (105,'Grog Shop','2785 Euclid Heights Blvd','','Cleveland','OH','USA','44106','http://www.grogshop.gs/','(216) 321-5588',1316730630);

INSERT INTO `calendar_venues` (`id`, `name`, `address1`, `address2`, `city`, `region`, `country`, `postalcode`, `url`, `phone`, `creation_date`) 
VALUES (106,'Lee’s Palace','529 Bloor Street W','','Toronto','ON','Canada','M5S 1Y4','http://www.leespalace.com/','(416) 532-1598',1316730630);

INSERT INTO `calendar_venues` (`id`, `name`, `address1`, `address2`, `city`, `region`, `country`, `postalcode`, `url`, `phone`, `creation_date`) 
VALUES (107,'Paradise','967 Commonwealth Avenue','','Boston','MA','USA','02215-1305','http://www.thedise.com/','(617) 562-8800',1316730630);

INSERT INTO `calendar_venues` (`id`, `name`, `address1`, `address2`, `city`, `region`, `country`, `postalcode`, `url`, `phone`, `creation_date`) 
VALUES (108,'Bell House','149 7th Street','','Brooklyn','NY','USA','11215','http://www.thebellhouseny.com/','(718) 643-6510',1316730630);

INSERT INTO `calendar_venues` (`id`, `name`, `address1`, `address2`, `city`, `region`, `country`, `postalcode`, `url`, `phone`, `creation_date`) 
VALUES (109,'Maxwell’s','1039 Washington Street','','Hoboken','NJ','USA','07030','http://maxwellsnj.com/','(201)653-1703',1316730630);

INSERT INTO `calendar_venues` (`id`, `name`, `address1`, `address2`, `city`, `region`, `country`, `postalcode`, `url`, `phone`, `creation_date`) 
VALUES (110,'Bowery Ballroom ','6 Delancey Street','','New York','NY','USA','10002-2804','http://boweryballroom.com/','(212) 533-2111',1316730630);

INSERT INTO `calendar_venues` (`id`, `name`, `address1`, `address2`, `city`, `region`, `country`, `postalcode`, `url`, `phone`, `creation_date`) 
VALUES (111,'Union Transfer','1026 Spring Garden Street','','Philadelphia','PA','USA','19123','http://www.utphilly.com/','(215) 232-2100',1316730630);

INSERT INTO `calendar_venues` (`id`, `name`, `address1`, `address2`, `city`, `region`, `country`, `postalcode`, `url`, `phone`, `creation_date`) 
VALUES (112,'Black Cat','1811 14th St NW','','Washington','DC','USA','20009','http://www.blackcatdc.com/','(202) 667-4490',1316730630);

INSERT INTO `calendar_venues` (`id`, `name`, `address1`, `address2`, `city`, `region`, `country`, `postalcode`, `url`, `phone`, `creation_date`) 
VALUES (113,'Cats Cradle','300 East Main Street','','Carrboro','NC','USA','27510','http://www.catscradle.com/','(919) 967-9053',1316730630);

INSERT INTO `calendar_venues` (`id`, `name`, `address1`, `address2`, `city`, `region`, `country`, `postalcode`, `url`, `phone`, `creation_date`) 
VALUES (114,'40 Watt','285 West Washington Street','','Athens','GA','USA','30601-2754','http://www.40watt.com/','(706) 549-7871',1316730630);

INSERT INTO `calendar_venues` (`id`, `name`, `address1`, `address2`, `city`, `region`, `country`, `postalcode`, `url`, `phone`, `creation_date`) 
VALUES (115,'Bottletree','3719 3rd Avenue South','','Birmingham','AL','USA','35222','http://www.thebottletree.com/','(205) 533-6288',1316730630);

INSERT INTO `calendar_venues` (`id`, `name`, `address1`, `address2`, `city`, `region`, `country`, `postalcode`, `url`, `phone`, `creation_date`) 
VALUES (116,'One Eyed Jacks','615 Toulouse Street','','New Orleans','LA','USA','70130-2125','http://www.oneeyedjacks.net/','(504) 569-8361',1316730630);

INSERT INTO `calendar_venues` (`id`, `name`, `address1`, `address2`, `city`, `region`, `country`, `postalcode`, `url`, `phone`, `creation_date`) 
VALUES (117,'Fitzgeralds Downstairs','2706 White Oak Drive','','Houston','TX','USA','77007-2792','http://fitzlivemusic.com/','(713) 862-3838',1316730630);

INSERT INTO `calendar_venues` (`id`, `name`, `address1`, `address2`, `city`, `region`, `country`, `postalcode`, `url`, `phone`, `creation_date`) 
VALUES (118,'The Loft','1135 South Lamar Street','','Dallas','TX','USA','(214) 421-2021','http://www.gilleysmusic.com/category/events/the-loft/','',1316730630);

INSERT INTO `calendar_venues` (`id`, `name`, `address1`, `address2`, `city`, `region`, `country`, `postalcode`, `url`, `phone`, `creation_date`) 
VALUES (119,'La Zona Rosa','612 West 4th Street','','Austin','TX','USA','78701','http://www.lazonarosa.com/','(512) 651-5033',1316730630);

INSERT INTO `calendar_venues` (`id`, `name`, `address1`, `address2`, `city`, `region`, `country`, `postalcode`, `url`, `phone`, `creation_date`) 
VALUES (120,'Rhythm Room','1019 E. Indian School Road','','Pheonix','AZ','USA','85014','http://rhythmroom.com/','(602)265-4842',1316730630);

INSERT INTO `calendar_venues` (`id`, `name`, `address1`, `address2`, `city`, `region`, `country`, `postalcode`, `url`, `phone`, `creation_date`) 
VALUES (121,'Casbah','2501 Kettner Boulevard','','San Diego','CA','USA','92101','http://www.casbahmusic.com/','(619) 232-4355',1316730630);

INSERT INTO `calendar_venues` (`id`, `name`, `address1`, `address2`, `city`, `region`, `country`, `postalcode`, `url`, `phone`, `creation_date`) 
VALUES (122,'Troubadour','9081 Santa Monica Boulevard','','Los Angeles','CA','USA','90069','http://www.troubadour.com/','(310) 276-6168',1316730630);

INSERT INTO `calendar_venues` (`id`, `name`, `address1`, `address2`, `city`, `region`, `country`, `postalcode`, `url`, `phone`, `creation_date`) 
VALUES (123,'Great American Music Hall','859 O’Farrell Street','','San Francisco','CA','USA','94109-7005','http://www.musichallsf.com/','(415) 885-0750',1316730630);

INSERT INTO `calendar_venues` (`id`, `name`, `address1`, `address2`, `city`, `region`, `country`, `postalcode`, `url`, `phone`, `creation_date`) 
VALUES (124,'Humboldt State University','1 Harpst Street','(707) 826-4411','Arcata','CA','USA','95521','http://www.humboldt.edu/centerarts/','',1316730630);

INSERT INTO `calendar_venues` (`id`, `name`, `address1`, `address2`, `city`, `region`, `country`, `postalcode`, `url`, `phone`, `creation_date`) 
VALUES (125,'Doug Fir','830 East Burnside Street','','Portland','OR','USA','97214','http://www.dougfirlounge.com/','(503) 231-9663',1316730630);

INSERT INTO `calendar_venues` (`id`, `name`, `address1`, `address2`, `city`, `region`, `country`, `postalcode`, `url`, `phone`, `creation_date`) 
VALUES (126,'Neumo’s','925 East Pike Street','','Seattle','WA','USA','98122','http://neumos.com/','(206) 709-9442',1316730630);

INSERT INTO `calendar_venues` (`id`, `name`, `address1`, `address2`, `city`, `region`, `country`, `postalcode`, `url`, `phone`, `creation_date`) 
VALUES (127,'Biltmore Caberet','395 Kingsway','','Vancouver','BC','Canada','V5T 3J5','http://www.biltmorecabaret.com/','(604) 676-0541',1316730630);

INSERT INTO `calendar_venues` (`id`, `name`, `address1`, `address2`, `city`, `region`, `country`, `postalcode`, `url`, `phone`, `creation_date`) 
VALUES (128,'Wesleyan University','','','Middletown','CT','USA','06459-0442','http://www.wesleyan.edu/','',1316730630);


INSERT INTO `calendar_events` (`id`, `date`, `user_id`, `venue_id`, `published`, `cancelled`, `purchase_url`, `comments`, `creation_date`) 
VALUES (100,1317700800,1,100,1,0,'http://www.ticketfly.com/event/48759/','w/ Yellow Fever',1316730630);

INSERT INTO `calendar_events` (`id`, `date`, `user_id`, `venue_id`, `published`, `cancelled`, `purchase_url`, `comments`, `creation_date`) 
VALUES (101,1317787200,1,101,1,0,'http://www.etix.com/ticket/online/performanceSearch.jsp?performance_id=1505285&cobrand=1percent','w/ Yellow Fever',1316730630);

INSERT INTO `calendar_events` (`id`, `date`, `user_id`, `venue_id`, `published`, `cancelled`, `purchase_url`, `comments`, `creation_date`) 
VALUES (102,1317873600,1,102,1,0,'https://holdmyticket.com/checkout/event/26057','w/ Yellow Fever',1316730630);

INSERT INTO `calendar_events` (`id`, `date`, `user_id`, `venue_id`, `published`, `cancelled`, `purchase_url`, `comments`, `creation_date`) 
VALUES (103,1318046400,1,103,1,0,'http://www.ticketfusion.com/store/one/index.html?store_id=2153&master_store_id=2153&page_type=ticket&show_id=593912&qid=19117255962&cid=','w/ Yellow Fever',1316730630);

INSERT INTO `calendar_events` (`id`, `date`, `user_id`, `venue_id`, `published`, `cancelled`, `purchase_url`, `comments`, `creation_date`) 
VALUES (104,1318132800,1,104,1,0,'','w/ Yellow Fever',1316730630);

INSERT INTO `calendar_events` (`id`, `date`, `user_id`, `venue_id`, `published`, `cancelled`, `purchase_url`, `comments`, `creation_date`) 
VALUES (105,1318219200,1,104,1,0,'','',1316730630);

INSERT INTO `calendar_events` (`id`, `date`, `user_id`, `venue_id`, `published`, `cancelled`, `purchase_url`, `comments`, `creation_date`) 
VALUES (106,1318305600,1,105,1,0,'http://www.ticketweb.com/t3/sale/SaleEventDetail?dispatch=loadSelectionData&eventId=3756565','w/ Yellow Fever',1316730630);

INSERT INTO `calendar_events` (`id`, `date`, `user_id`, `venue_id`, `published`, `cancelled`, `purchase_url`, `comments`, `creation_date`) 
VALUES (107,1318392000,1,106,1,0,'http://www.ticketmaster.ca/event/100046D4E212736E?artistid=1504817&majorcatid=10001&minorcatid=1','w/ Yellow Fever',1316730630);

INSERT INTO `calendar_events` (`id`, `date`, `user_id`, `venue_id`, `published`, `cancelled`, `purchase_url`, `comments`, `creation_date`) 
VALUES (108,1318651200,1,107,1,0,'http://www.ticketmaster.com/event/010046CE151ABB55','w/ Eleanor Friedberger',1316730630);

INSERT INTO `calendar_events` (`id`, `date`, `user_id`, `venue_id`, `published`, `cancelled`, `purchase_url`, `comments`, `creation_date`) 
VALUES (109,1318737600,1,108,1,0,'','w/ Eleanor Friedberger',1316730630);

INSERT INTO `calendar_events` (`id`, `date`, `user_id`, `venue_id`, `published`, `cancelled`, `purchase_url`, `comments`, `creation_date`) 
VALUES (110,1318824000,1,109,1,0,'','w/ Eleanor Friedberger',1316730630);

INSERT INTO `calendar_events` (`id`, `date`, `user_id`, `venue_id`, `published`, `cancelled`, `purchase_url`, `comments`, `creation_date`) 
VALUES (111,1318996800,1,110,1,0,'','w/ Eleanor Friedberger and Hospitality',1316730630);

INSERT INTO `calendar_events` (`id`, `date`, `user_id`, `venue_id`, `published`, `cancelled`, `purchase_url`, `comments`, `creation_date`) 
VALUES (112,1319083200,1,111,1,0,'http://www.ticketfly.com/purchase/event/51551?__utma=1.553749205.1316712443.1316712443.1316712443.1&__utmb=1.1.10.1316712443&__utmc=1&__utmx=-&__utmz=1.1316712443.1.1.utmcsr=google|utmccn=(organic)|utmcmd=organic|utmctr=union%20transfer%20philadelphia&__utmv=-&__utmk=168549921','w/ Eleanor Friedberger',1316730630);

INSERT INTO `calendar_events` (`id`, `date`, `user_id`, `venue_id`, `published`, `cancelled`, `purchase_url`, `comments`, `creation_date`) 
VALUES (113,1319169600,1,112,1,0,'','w/ Eleanor Friedberger',1316730630);

INSERT INTO `calendar_events` (`id`, `date`, `user_id`, `venue_id`, `published`, `cancelled`, `purchase_url`, `comments`, `creation_date`) 
VALUES (114,1319256000,1,113,1,0,'http://www.etix.com/ticket/online/performanceSearch.jsp?performance_id=1503189','w/ Eleanor Friedberger',1316730630);

INSERT INTO `calendar_events` (`id`, `date`, `user_id`, `venue_id`, `published`, `cancelled`, `purchase_url`, `comments`, `creation_date`) 
VALUES (115,1319342400,1,114,1,0,'http://www.pitchatent.com/Merchant2/merchant.mvc?Store_Code=01&Screen=PROD&Category_Code=40watt&Product_Code=TIX680','w/ Eleanor Friedberger',1316730630);

INSERT INTO `calendar_events` (`id`, `date`, `user_id`, `venue_id`, `published`, `cancelled`, `purchase_url`, `comments`, `creation_date`) 
VALUES (116,1319515200,1,115,1,0,'http://www.thebottletree.com/?Page=http%3a%2f%2fpublic.ticketbiscuit.com%2fBottleTree%2fTicketing%2f101155','w/ Eleanor Friedberger',1316730630);

INSERT INTO `calendar_events` (`id`, `date`, `user_id`, `venue_id`, `published`, `cancelled`, `purchase_url`, `comments`, `creation_date`) 
VALUES (117,1319688000,1,116,1,0,'http://www.ticketweb.com/t3/sale/SaleEventDetail?dispatch=loadSelectionData&eventId=3757315','w/ Eleanor Friedberger',1316730630);

INSERT INTO `calendar_events` (`id`, `date`, `user_id`, `venue_id`, `published`, `cancelled`, `purchase_url`, `comments`, `creation_date`) 
VALUES (118,1319774400,1,117,1,0,'https://www.stubwire.com/order/options.php?order=265340daf9b35b7e6372d35812027d80','',1316730630);

INSERT INTO `calendar_events` (`id`, `date`, `user_id`, `venue_id`, `published`, `cancelled`, `purchase_url`, `comments`, `creation_date`) 
VALUES (119,1319860800,1,118,1,0,'http://tactics.frontgatetickets.com/choose.php?a=1&lid=56744&eid=64726&utm_source=&utm_medium=social-media&utm_content=loft-ticketlink-top&utm_campaign=wildflag','w/ Drew Grow &amp; the Pastors’ Wives',1316730630);

INSERT INTO `calendar_events` (`id`, `date`, `user_id`, `venue_id`, `published`, `cancelled`, `purchase_url`, `comments`, `creation_date`) 
VALUES (120,1319947200,1,119,1,0,'http://lazonarosa.frontgatetickets.com/choose.php?a=1&lid=59871&eid=68122','w/ Drew Grow &amp; the Pastors’ Wives',1316730630);

INSERT INTO `calendar_events` (`id`, `date`, `user_id`, `venue_id`, `published`, `cancelled`, `purchase_url`, `comments`, `creation_date`) 
VALUES (121,1320120000,1,120,1,0,'','w/ Drew Grow &amp; the Pastors’ Wives',1316730630);

INSERT INTO `calendar_events` (`id`, `date`, `user_id`, `venue_id`, `published`, `cancelled`, `purchase_url`, `comments`, `creation_date`) 
VALUES (122,1320206400,1,121,1,0,'http://casbah.frontgatetickets.com/choose.php?a=1&lid=56489&eid=64452','w/ Drew Grow &amp; the Pastors’ Wives',1316730630);

INSERT INTO `calendar_events` (`id`, `date`, `user_id`, `venue_id`, `published`, `cancelled`, `purchase_url`, `comments`, `creation_date`) 
VALUES (123,1320292800,1,122,1,0,'http://www.ticketfly.com/purchase/event/49021?__utma=1.243892687.1316716918.1316716918.1316716918.1&__utmb=1.2.10.1316716918&__utmc=1&__utmx=-&__utmz=1.1316716918.1.1.utmcsr=google|utmccn=(organic)|utmcmd=organic|utmctr=Troubadour%20los%20angeles&__utmv=-&__utmk=167633214','w/ Drew Grow &amp; the Pastors’ Wives',1316730630);

INSERT INTO `calendar_events` (`id`, `date`, `user_id`, `venue_id`, `published`, `cancelled`, `purchase_url`, `comments`, `creation_date`) 
VALUES (124,1320379200,1,122,1,0,'http://www.ticketfly.com/purchase/event/49025?__utma=1.243892687.1316716918.1316716918.1316716918.1&__utmb=1.2.10.1316716918&__utmc=1&__utmx=-&__utmz=1.1316716918.1.1.utmcsr=google|utmccn=(organic)|utmcmd=organic|utmctr=Troubadour%20los%20angeles&__utmv=-&__utmk=167633214','w/ Drew Grow &amp; the Pastors’ Wives',1316730630);

INSERT INTO `calendar_events` (`id`, `date`, `user_id`, `venue_id`, `published`, `cancelled`, `purchase_url`, `comments`, `creation_date`) 
VALUES (125,1320465600,1,123,1,0,'http://www.gamhtickets.com/events/155665/Wild%20Flag','w/ Drew Grow &amp; the Pastors’ Wives',1316730630);

INSERT INTO `calendar_events` (`id`, `date`, `user_id`, `venue_id`, `published`, `cancelled`, `purchase_url`, `comments`, `creation_date`) 
VALUES (126,1320552000,1,123,1,0,'http://www.gamhtickets.com/events/155666/Wild%20Flag','w/ Drew Grow &amp; the Pastors’ Wives',1316730630);

INSERT INTO `calendar_events` (`id`, `date`, `user_id`, `venue_id`, `published`, `cancelled`, `purchase_url`, `comments`, `creation_date`) 
VALUES (127,1320724800,1,124,1,0,'','w/ Drew Grow &amp; the Pastors’ Wives',1316730630);

INSERT INTO `calendar_events` (`id`, `date`, `user_id`, `venue_id`, `published`, `cancelled`, `purchase_url`, `comments`, `creation_date`) 
VALUES (128,1320897600,1,125,1,0,'http://www.ticketfly.com/purchase/event/48245?utm_medium=bks','w/ Drew Grow &amp; the Pastors’ Wives',1316730630);

INSERT INTO `calendar_events` (`id`, `date`, `user_id`, `venue_id`, `published`, `cancelled`, `purchase_url`, `comments`, `creation_date`) 
VALUES (129,1320984000,1,125,1,0,'http://www.ticketfly.com/purchase/event/48247?utm_medium=bks','w/ Drew Grow &amp; the Pastors’ Wives',1316730630);

INSERT INTO `calendar_events` (`id`, `date`, `user_id`, `venue_id`, `published`, `cancelled`, `purchase_url`, `comments`, `creation_date`) 
VALUES (130,1321070400,1,126,1,0,'https://www.etix.com/ticket/online/performanceSearch.jsp?performance_id=1504572&cobrand=neumos','w/ Drew Grow &amp; the Pastors’ Wives',1316730630);

INSERT INTO `calendar_events` (`id`, `date`, `user_id`, `venue_id`, `published`, `cancelled`, `purchase_url`, `comments`, `creation_date`) 
VALUES (131,1321156800,1,127,1,0,'http://www.ticketweb.ca/t3/sale/SaleEventDetail?dispatch=loadSelectionData&eventId=3757025','w/ Drew Grow &amp; the Pastors’ Wives',1316730630);

INSERT INTO `calendar_events` (`id`, `date`, `user_id`, `venue_id`, `published`, `cancelled`, `purchase_url`, `comments`, `creation_date`) 
VALUES (132,1318554000,1,128,1,0,'','',1316730630);
