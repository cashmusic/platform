INSERT INTO `asst_assets` (`id`, `user_id`, `parent_id`, `location`, `settings_id`, `title`, `description`, `public_status`, `creation_date`, `modification_date`)
VALUES (100, 1, 0, 'http://cashmusic.s3.amazonaws.com/permalink/demos/portugaltheman/WhenTheWarEnds_987yh.mp3', 0, '"When The World Ends" MP3', '320kbps MP3', 1, 1311928519, 0);

INSERT INTO `elmt_elements` (`id`, `user_id`, `name`, `type`, `options`, `license_id`, `creation_date`, `modification_date`)
VALUES (100, 1, 'Portugal. The Man email for download', 'emailcollection', '{"message_invalid_email":"That email address wasn’t valid. Try again.","message_privacy":"We won’t share, sell, or be jerks with your email address. Promise.","message_success":"Thanks! You’re all signed up. Here’s your download:","emal_list_id":"100","asset_id":"100","comment_or_radio":null,"do_not_verify":"1"}', 0, 1295306566, 1315552494);

INSERT INTO `elmt_elements` (`id`, `user_id`, `name`, `type`, `options`, `license_id`, `creation_date`, `modification_date`)
VALUES (101, 1, 'Iron & Wine ticket contest', 'emailcollection', '{"message_invalid_email":"Sorry, that email address wasn’t valid. Please try again.","message_privacy":"Your email won’t be shared and we’ll use it respectfully. ","message_success":"Thanks! You’re all signed up. Winners will be emailed prior to the concert.","emal_list_id":"101","asset_id":"0","comment_or_radio":null,"do_not_verify":"1"}', 0, 1315519963, NULL);

INSERT INTO `elmt_elements` (`id`, `user_id`, `name`, `type`, `options`, `license_id`, `creation_date`, `modification_date`)
VALUES (102, 1, 'Wild Flag Tour Dates', 'tourdates', '{"visible_event_types":"upcoming"}', 0, 1316730460, NULL);

INSERT INTO `user_lists` (`id`, `name`, `description`, `user_id`, `settings_id`, `creation_date`, `modification_date`)
VALUES (100, 'Portugal List', 'The demo list for Portugal. The Man’s email download demo.', 1, 0, 1311316210, 0);

INSERT INTO `user_lists` (`id`, `name`, `description`, `user_id`, `settings_id`, `creation_date`, `modification_date`)
VALUES (101, 'Iron & Wine List', 'The demo list for Iron & Wine’s email contest demo.', 1, 0, 1313045289, 0);

INSERT INTO `user_lists` (`id`, `name`, `description`, `user_id`, `settings_id`, `creation_date`, `modification_date`)
VALUES (99, 'no-user list', 'This list is assigned to a non-existent user', 123, 0, 1313045389, 0);

INSERT INTO `live_venues` (`id`, `name`, `address1`, `address2`, `city`, `region`, `country`, `postalcode`, `url`, `phone`, `creation_date`) 
VALUES (100,'Varsity Theatre','1308 4TH STREET SE','','Minneapolis','MN','USA','55414','http://www.varsitytheater.org/','(612) 604-0222',1316730630);

INSERT INTO `live_venues` (`id`, `name`, `address1`, `address2`, `city`, `region`, `country`, `postalcode`, `url`, `phone`, `creation_date`) 
VALUES (101,'Waiting Room','6212 Maple Street','','Omaha','NE','USA','68104','http://www.waitingroomlounge.com/','(402) 884-5353',1316730630);

INSERT INTO `live_venues` (`id`, `name`, `address1`, `address2`, `city`, `region`, `country`, `postalcode`, `url`, `phone`, `creation_date`) 
VALUES (102,'Record Bar','1020 Westport Road','','Kansas City','MO','USA','64111','http://www.therecordbar.com/','(816) 753-5207',1316730630);

INSERT INTO `elmt_elements` (`id`, `user_id`, `name`, `type`, `options`, `license_id`, `creation_date`, `modification_date`)
VALUES (103, 1, 'Palmer/Gaiman filtered social feeds', 'socialfeeds', '{"tumblr":[],"twitter":[{"twitterusername":"neilhimself","twitterhidereplies":false,"twitterfiltertype":"beginwith","twitterfiltervalue":"@amandapalmer"},{"twitterusername":"amandapalmer","twitterhidereplies":false,"twitterfiltertype":"beginwith","twitterfiltervalue":"@neilhimself"}]}', 0, 1317978017, NULL);
